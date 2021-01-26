<?php


use common\models\project\Chapter;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $project common\models\project\Project */
/* @var $searchModel common\models\project\ChapterSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>

<div class="project-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'resizableColumns' => false,
        'toolbar' => [
            '{toggleData}&nbsp;{export}',
        ],
        'exportConfig' => [
            GridView::CSV => [
                'label' => 'TSV',
                'mime' => 'application/tsv',
                'config' => [
                    'colDelimiter' => "\t",
                    'rowDelimiter' => "\r\n",
                ]
            ],
            GridView::TEXT => [],
            GridView::EXCEL => [],
            GridView::JSON => [],
        ],
        'panel' => [
            'type' => 'default',
            'before' => Html::a(
                    '<i class="fas fa-plus"></i> ' . Yii::t('app', 'Add Chapter'),
                    ['/chapter/create', 'project_id' => $project->id],
                    ['class' => 'btn btn-sm btn-success']) . ' ' .
                Html::a(
                    '<i class="fas fa-file-import"></i> ' . Yii::t('app', 'Import Chapters'),
                    ['/chapter/import', 'project_id' => $project->id],
                    ['class' => 'btn btn-sm btn-info']),
            'after' => false,
            'footer' => ''
        ],
        'columns' => [
            [
                'class' => '\kartik\grid\CheckboxColumn'
            ],
            [
                'attribute' => 'priority',
                'label' => '#',
                'value' => function (Chapter $model) {
                    $value = $model->priority;
                    if ($model->url) {
                        $value .= "&nbsp;" . Html::a('<span class="fas fa-file-alt"></span>', $model->url,
                                ['target' => '_blank']);
                    }
                    return $value;
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'url',
                'hidden' => true,
            ],
            [
                'attribute' => 'title',
                'value' => function (Chapter $model) {
                    $title = $model->title ? $model->title : Yii::$app->formatter->nullDisplay;
                    return Html::a($title, ['/chapter/view', 'id' => $model->id]);
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'word_count',
                'value' => function (Chapter $model) {
                    return $model->word_count ? Yii::$app->formatter->asInteger($model->word_count) : '—';
                }
            ],
            [
                'attribute' => 'edit_count',
                'value' => function (Chapter $model) {
                    return $model->edit_count ? Yii::$app->formatter->asInteger($model->edit_count) : '—';
                }
            ],
            [
                'attribute' => 'status',
                'value' => function (Chapter $model) {
                    $value = Chapter::statusLabels()[$model->status];
                    if ($model->status == Chapter::STATUS_ERROR) {
                        $value = Html::tag('span', $value,
                            ['class' => 'badge badge-danger', 'title' => $model->error_message]);
                    }
                    return $value;
                },
                'filter' => Chapter::statusLabels(),
                'format' => 'raw',
            ],
            'updated_at:datetime',
            [
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{update} {delete}',
                'urlCreator' => function ($action, $model, $key, $index) {
                    $url = Url::to(['/chapter/' . $action, 'id' => $model->id]);
                    return $url;
                }
            ],
        ],
    ]); ?>
</div>
