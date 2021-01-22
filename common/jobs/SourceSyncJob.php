<?php

namespace common\jobs;

use common\models\project\Source;
use Google_Service_Docs;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Sync source
 *
 * @package common\jobs
 */
class SourceSyncJob extends BaseObject implements JobInterface
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
        echo "title: "; var_dump($source->title); echo "\n";
        $source->save();
    }
}
