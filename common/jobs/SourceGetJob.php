<?php

namespace common\jobs;

use common\models\project\Source;
use common\models\project\SourceParagraph;
use Google_Service_Docs;
use Yii;
use yii\base\BaseObject;
use yii\base\Exception;
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
            return;
        }

        if ($source->locked_until > time()) {
            // locked by another job
            return;
        }

        $rowCount = Source::updateAll(
            [
                'locked_until' => time() + self::LOCK_TIME,
                'updated_at' => time(),
                'status' => Source::STATUS_GET,
                'error_message' => null,
            ],
            [
                'id' => $source->id,
                'updated_at' => $source->updated_at,
            ],
        );

        if (!$rowCount) {
            Yii::error('Lock failed: ' . $this->source_id);
            return;
        }

        if (!($accessToken = $source->project->accessToken)) {

            $error_message = 'Project has no access token';
            Yii::error($error_message);
            $source->error_message = $error_message;
            $source->status = Source::STATUS_ERROR;
            $source->locked_until = 0;
            $source->save();

            return;
        }

        if (!($client = $source->project->getGoogleClient())) {
            $error_message = 'Could not get project google client';
            Yii::error($error_message);
            $source->error_message = $error_message;
            $source->status = Source::STATUS_ERROR;
            $source->locked_until = 0;
            $source->save();
            return;
        }

        if (!preg_match('`/document/d/([^/&?]+)`', $source->url, $r)) {
            $error_message = 'Invalid source URL';
            Yii::error($error_message);
            $source->error_message = $error_message;
            $source->status = Source::STATUS_ERROR;
            $source->locked_until = 0;
            $source->save();
            return;
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

            Yii::error($error_message);
            $source->error_message = $error_message;
            $source->status = Source::STATUS_ERROR;
            $source->locked_until = 0;
            $source->save();

            return;
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

                    $pContent .= $textRun->getContent();
                }

                if (trim($pContent) == '') {
                    continue;
                }

                if (!$source->title) {
                    $source->title = $pContent;
                    continue;
                }

                $paragraphs[] = $pContent;
            }
        }

        $transaction = SourceParagraph::getDb()->beginTransaction();
        try {
            SourceParagraph::deleteAll(['source_id' => $source->id]);
            foreach ($paragraphs as $i => $paragraph) {
                $sp = new SourceParagraph([
                    'source_id' => $source->id,
                    'priority' => $i + 1,
                    'content' => $paragraph,
                ]);
                $sp->save();
                $wordCount += count(preg_split('~[^\p{L}\p{N}\']+~u', $paragraph));
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();

            Yii::error($e->getMessage());
            $source->error_message = $e->getMessage();
            $source->status = Source::STATUS_ERROR;
            $source->locked_until = 0;
            $source->save();

            return;
        }

        $source->word_count = $wordCount;
        $source->edit_count = count(array_unique($suggestionIds));
        $source->status = Source::STATUS_OK;
        $source->locked_until = 0;
        $source->save();
    }
}
