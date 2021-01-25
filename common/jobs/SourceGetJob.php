<?php

namespace common\jobs;

use common\models\project\Source;
use common\models\project\SourceParagraph;
use Google_Service_Docs;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Json;
use yii\queue\JobInterface;

/**
 * Get source
 *
 * @package common\jobs
 */
class SourceGetJob extends BaseObject implements JobInterface
{
    const LOCK_TIME = 60;

    /** @var integer */
    public int $source_id;

    public function execute($queue)
    {
        $source = Source::findOne($this->source_id);
        if (!$source) {
            Yii::error('Could not find source ' . $this->source_id);
            return false;
        }

        if ($source->locked_until > time()) {
            // locked by another job
            return false;
        }

        if (!$source->lock(self::LOCK_TIME)) {
            Yii::error('Lock failed: ' . $this->source_id);
            return false;
        }

        if (!($accessToken = $source->project->accessToken)) {
            $source->setError('Project has no access token');
            return false;
        }

        if (!($client = $source->project->getGoogleClient())) {
            $source->setError('Could not get project google client');
            return false;
        }

        if (!preg_match('`/document/d/([^/&?]+)`', $source->url, $r)) {
            $source->setError('Invalid source URL');
            return false;
        }

        $documentId = $r[1];

        $service = new Google_Service_Docs($client);

        try {
            $doc = $service->documents->get($documentId);
        } catch (\Google\Service\Exception $e) {
            $error_message = $e->getMessage();
            try {
                $data = Json::decode($error_message);
                $error_message = $data['error']['message'];
            } catch (\Exception $e) {
            }
            $source->setError($error_message);
            return false;
        }

        $source->title = null;

        $content = $doc->getBody()->getContent();
        $paragraphs = [];
        $suggestionIds = [];
        $wordCount = 0;
        foreach ($content as $contentElement) {
            if ($p = $contentElement->getParagraph()) {
                $pContent = '';
                foreach ($p->getElements() as $element) {
                    $textRun = $element->getTextRun();

                    if ($textRun->suggestedInsertionIds) {
                        $suggestionIds = array_merge($suggestionIds, $textRun->suggestedInsertionIds);
                        // suggested fragment, ignore
                        continue;
                    }

                    if ($textRun->suggestedDeletionIds) {
                        $suggestionIds = array_merge($suggestionIds, $textRun->suggestedDeletionIds);
                    }

                    $style = $textRun->getTextStyle();
                    $c = $textRun->getContent();
                    if ($style->bold) {
                        $c = '<b>' . $c . '</b>';
                    }
                    if ($style->italic) {
                        $c = '<em>' . $c . '</em>';
                    }
                    if ($style->underline) {
                        $c = '<u>' . $c . '</u>';
                    }
                    $pContent .= $c;
                }

                if (trim($pContent) == '') {
                    continue;
                }

                if (!$source->title) {
                    $source->title = $pContent;
                    continue;
                }

                $paragraphs[] = $pContent;
                $wordCount += count(preg_split('~[^\p{L}\p{N}\']+~u', $pContent));
            }
        }

        SourceParagraph::deleteAll(['source_id' => $source->id]);
        foreach ($paragraphs as $i => $paragraph) {
            $sp = new SourceParagraph([
                'source_id' => $source->id,
                'priority' => $i + 1,
                'content' => $paragraph,
            ]);
            $sp->save();
        }

        $source->word_count = $wordCount;
        $source->edit_count = count(array_unique($suggestionIds));
        $source->status = Source::STATUS_OK;
        $source->locked_until = 0;
        $source->save();

        return true;
    }
}
