<?php

namespace console\controllers;

use common\jobs\ChapterGetJob;
use common\models\chapter\Chapter;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\queue\Queue;

/**
 * Refresh chapter content (cron task)
 *
 * php yii chapter-get
 *
 * @package console\controllers
 */
class ChapterGetController extends Controller
{

    public function init()
    {
        parent::init();;
    }

    public function actionIndex()
    {
        $chapter_ids = Chapter::find()
            ->select('id')
            ->where('(status = :new OR updated_at < :time) 
                AND status <> :waiting AND status <> :export 
                AND (status <> :error OR error_message = :error1 OR error_message = :error2)',
                [
                    'new' => Chapter::STATUS_NEW,
                    'waiting' => Chapter::STATUS_WAITING,
                    'export' => Chapter::STATUS_PUBLICATION,
                    'error' => Chapter::STATUS_ERROR,
                    'time' => time() - Yii::$app->params['chapter.getTimeout'],
                    // these are temporary
                    'error1' => 'Internal error encountered.',
                    'error2' => 'The service is currently unavailable.',
                ])
            ->createCommand()
            ->queryColumn();

        Chapter::updateAll(['status' => Chapter::STATUS_WAITING], ['id' => $chapter_ids]);
        /** @var Queue $queue */
        $queue = Yii::$app->get('queue');
        foreach ($chapter_ids as $chapter_id) {
            $queue->push(new ChapterGetJob(['chapter_id' => $chapter_id]));
        }

        return ExitCode::OK;
    }

}
