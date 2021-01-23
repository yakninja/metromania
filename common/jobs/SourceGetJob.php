<?php

namespace common\jobs;

use common\models\project\Source;
use common\models\project\SourceParagraph;
use Google_Service_Docs;
use Yii;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\queue\JobInterface;

/**
 * Get source
 *
 * @package common\jobs
 */
class SourceGetJob extends BaseObject implements JobInterface
{
    /** @var integer */
    public int $source_id;

    public function execute($queue)
    {
        $source = Source::findOne($this->source_id);
        if (!$source) {
            Yii::error('Could not find source ' . $this->source_id);
            return;
        }

        if (!($accessToken = $source->project->accessToken)) {
            Yii::error('Project has no access token');
            return;
        }

        if (!($client = $source->project->getGoogleClient())) {
            Yii::error('Could not get project google client');
            return;
        }

        if (!preg_match('`/document/d/([^/&?]+)`', $source->url, $r)) {
            Yii::error('Invalid source URL');
            return;
        }

        $documentId = $r[1];

        $service = new Google_Service_Docs($client);

        $doc = $service->documents->get($documentId);
        $source->title = $doc->getTitle();
        $source->save();

        $content = $doc->getBody()->getContent();
        $paragraphs = [];
        foreach ($content as $contentElement) {
            if ($p = $contentElement->getParagraph()) {
                $pContent = '';
                foreach ($p->getElements() as $element) {
                    $pContent .= $element->getTextRun()->getContent();
                }
                if (trim($pContent)) {
                    $paragraphs[] = $pContent;
                }
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
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
        }
    }
}
