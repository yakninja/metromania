<?php

namespace console\controllers;

use common\jobs\SourceGetJob;
use common\models\project\Source;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\queue\Queue;

/**
 * Refresh source content (cron task)
 *
 * php yii source-get
 *
 * @package console\controllers
 */
class SourceGetController extends Controller
{

    public function init()
    {
        parent::init();;
    }

    public function actionIndex()
    {
        $source_ids = Source::find()
            ->select('id')
            ->where(['<', 'updated_at', time() - Yii::$app->params['source.getTimeout']])
            ->andWhere(['<>', 'status', Source::STATUS_WAITING])
            ->createCommand()
            ->queryColumn();

        Source::updateAll(['status' => Source::STATUS_WAITING], ['id' => $source_ids]);
        /** @var Queue $queue */
        $queue = Yii::$app->get('queue');
        foreach ($source_ids as $source_id) {
            $queue->push(new SourceGetJob(['source_id' => $source_id]));
        }

        return ExitCode::OK;
    }

}
