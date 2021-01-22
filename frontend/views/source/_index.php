<?php


use common\models\project\Source;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $project common\models\project\Project */
/* @var $searchModel common\models\project\SourceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>

<div class="project-index">
    <p>
        <?= Html::a(Yii::t('app', 'Add Source'), ['/source/create', 'project_id' => $project->id], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'resizableColumns' => false,
        'columns' => [
            'title',
            [
                'attribute' => 'status',
                'value' => function (Source $model) {
                    return Source::statusLabels()[$model->status];
                }
            ],
            'updated_at:datetime',
            [
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{sync} {update} {delete}',
                'buttons' => [
                    'sync' => function ($url, $model) {
                        return Html::a('<span class="fas fa-sync"></span>', $url, [
                            'title' => Yii::t('app', 'Sync'),
                        ]);

                    }
                ],
                'urlCreator' => function ($action, $model, $key, $index) {
                    if ($action == 'sync') {
                        return Url::to(['/source/sync', 'site_id' => $model->id]);
                    }
                    $url = Url::to(['/source/' . $action, 'id' => $model->id]);
                    return $url;
                }
            ],
        ],
    ]); ?>
</div>
