<?php

namespace common\jobs;

use common\models\project\Chapter;
use common\models\project\ChapterParagraph;
use Google_Service_Docs;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Json;
use yii\queue\JobInterface;

/**
 * Get chapter
 *
 * @package common\jobs
 */
class ChapterGetJob extends BaseObject implements JobInterface
{
    const LOCK_TIME = 60;

    /** @var int */
    public int $chapter_id;

    public function execute($queue)
    {
        $chapter = Chapter::findOne($this->chapter_id);
        if (!$chapter) {
            Yii::error('Could not find chapter ' . $this->chapter_id);
            return false;
        }

        if ($chapter->locked_until > time()) {
            // locked by another job
            return false;
        }

        if (!$chapter->lock(self::LOCK_TIME)) {
            Yii::error('Lock failed: ' . $this->chapter_id);
            return false;
        }

        if (!($accessToken = $chapter->project->accessToken)) {
            $chapter->setError('Project has no access token');
            return false;
        }

        if (!($client = $chapter->project->getGoogleClient())) {
            $chapter->setError('Could not get project google client');
            return false;
        }

        if (!preg_match('`/document/d/([^/&?]+)`', $chapter->url, $r)) {
            $chapter->setError('Invalid chapter URL');
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
            $chapter->setError($error_message);
            return false;
        }

        $chapter->title = null;

        $content = $doc->getBody()->getContent();
        $paragraphs = [];
        $suggestionIds = [];
        $wordCount = 0;
        foreach ($content as $contentElement) {
            if ($p = $contentElement->getParagraph()) {
                $pContent = '';
                foreach ($p->getElements() as $element) {
                    $textRun = $element->getTextRun();
                    if (!$textRun) {
                        continue;
                    }

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
                    if ($chapter->title) {
                        if ($style->bold) {
                            $c = '<b>' . $c . '</b>';
                        }
                        if ($style->italic) {
                            $c = '<em>' . $c . '</em>';
                        }
                        if ($style->underline) {
                            $c = '<u>' . $c . '</u>';
                        }
                    }
                    $pContent .= $c;
                }

                if (trim($pContent) == '') {
                    continue;
                }

                if (!$chapter->title) {
                    // first paragraph is the title
                    $chapter->title = $pContent;
                    continue;
                }

                $paragraphs[] = $pContent;
                $wordCount += count(preg_split('~[^\p{L}\p{N}\']+~u', $pContent));
            }
        }

        ChapterParagraph::deleteAll(['chapter_id' => $chapter->id]);
        foreach ($paragraphs as $i => $paragraph) {
            $sp = new ChapterParagraph([
                'chapter_id' => $chapter->id,
                'priority' => $i + 1,
                'content' => $paragraph,
            ]);
            $sp->save();
        }

        $chapter->word_count = $wordCount;
        $chapter->edit_count = count(array_unique($suggestionIds));
        $chapter->status = Chapter::STATUS_OK;
        $chapter->locked_until = 0;
        $chapter->save();

        return true;
    }
}
