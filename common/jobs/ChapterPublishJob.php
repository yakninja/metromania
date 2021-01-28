<?php

namespace common\jobs;

use common\apis\Ficbook;
use common\models\chapter\Chapter;
use common\models\chapter\ChapterPublication;
use common\models\project\ProjectPublicationSettings;
use Yii;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\queue\JobInterface;

/**
 * Publish chapter
 *
 * @package common\jobs
 */
class ChapterPublishJob extends BaseObject implements JobInterface
{
    const LOCK_TIME = 60;

    /** @var int */
    public int $chapter_id;

    /** @var int|null */
    public $service_id;

    public function execute($queue)
    {
        $chapter = Chapter::findOne($this->chapter_id);
        if (!$chapter) {
            Yii::error('Could not find chapter ' . $this->chapter_id);
            return false;
        }

        if ($chapter->locked_until > time()) {
            // locked by another job
            echo "chapter locked by another job\n";
            return false;
        }

        if (!$chapter->lock(self::LOCK_TIME, Chapter::STATUS_PUBLICATION)) {
            Yii::error('Lock failed: ' . $this->chapter_id);
            return false;
        }
        echo "chapter locked\n";

        $query = ChapterPublication::find()->where(['chapter_id' => $this->chapter_id]);
        if ($this->service_id) {
            $query->andWhere(['service_id' => $this->service_id]);
        }
        /** @var ChapterPublication[] $publications */
        $publications = $query->all();

        foreach ($publications as $publication) {
            if ($publication->locked_until > time()) {
                echo "publication already locked\n";
                continue;
            }
            if (!$publication->lock(self::LOCK_TIME)) {
                Yii::error('Publication lock failed: ' . $this->chapter_id);
                continue;
            }
            $projectPublicationSettings = ProjectPublicationSettings::findOne([
                'project_id' => $chapter->project_id,
                'service_id' => $publication->service_id,
            ]);

            if (!$projectPublicationSettings) {
                $publication->setError('Project has no publication settings');
                continue;
            }

            try {
                /** @var Ficbook $api */
                $api = Yii::createObject($publication->service->api_class);
                echo "got api\n";
            } catch (Exception $e) {
                $publication->setError($e->getMessage());
                continue;
            }

            try {
                echo "logging in\n";
                if (!$api->login($projectPublicationSettings->username, $projectPublicationSettings->password)) {
                    $publication->setError('Could not log in');
                    continue;
                }
                echo "publishing\n";
                if (!$api->publish($publication->url, $chapter->title, $chapter->content)) {
                    $publication->setError('Could not publish');
                    continue;
                }
                echo "done\n";
            } catch (Exception $e) {
                $publication->setError($e->getMessage());
                continue;
            }

            $publication->status = ChapterPublication::STATUS_OK;
            $publication->locked_until = 0;
            $publication->published_at = time();
            $publication->hash = $publication->chapter->hash;
            $publication->save();
            echo "publication unlocked\n";
        }

        $chapter->status = Chapter::STATUS_OK;
        $chapter->locked_until = 0;
        $chapter->save();
        echo "chapter unlocked\n";

        return true;
    }
}
