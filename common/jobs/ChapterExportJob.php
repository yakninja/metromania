<?php

namespace common\jobs;

use common\models\project\Chapter;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Export chapter
 *
 * @package common\jobs
 */
class ChapterExportJob extends BaseObject implements JobInterface
{
    const LOCK_TIME = 60;

    /** @var int */
    public int $chapter_id;

    /** @var int */
    public int $export_provider_id;

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

        if (!$chapter->lock(self::LOCK_TIME, Chapter::STATUS_EXPORT)) {
            Yii::error('Lock failed: ' . $this->chapter_id);
            return false;
        }

        $chapter->status = Chapter::STATUS_OK;
        $chapter->locked_until = 0;
        $chapter->save();

        return true;
    }
}
