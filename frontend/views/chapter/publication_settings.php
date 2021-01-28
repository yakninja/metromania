<?php

use common\models\chapter\ChapterPublication;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\chapter\Chapter */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Chapter Publication Settings');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->project->name, 'url' => ['/project/view', 'id' => $model->project->id]];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['/chapter/view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="chapter-publication-settings">
    <p>
        <?= Html::a('<i class="fas fa-plus"></i> ' . Yii::t('app', 'Add Publication Setting'),
            ['create-publication-setting', 'chapter_id' => $model->id],
            ['class' => 'btn btn-sm btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'resizableColumns' => false,
        'columns' => [
            [
                'attribute' => 'service.name',
                'label' => Yii::t('app', 'Publication Service'),
            ],
            'url:url',
            [
                'attribute' => 'status',
                'value' => function (ChapterPublication $model) {
                    $value = ChapterPublication::statusLabels()[$model->status];
                    if ($model->status == ChapterPublication::STATUS_ERROR) {
                        $value = Html::tag('span', $value,
                            ['class' => 'badge badge-danger', 'title' => $model->error_message]);
                    }
                    return $value;
                },
                'filter' => ChapterPublication::statusLabels(),
                'format' => 'raw',
            ],
            [
                'attribute' => 'published_at',
                'value' => function (ChapterPublication $model) {
                    $f = Yii::$app->formatter;
                    return $model->published_at ?
                        $f->asDatetime($model->published_at) :
                        $f->nullDisplay;
                },
                'format' => 'raw',
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{publish} {update} {delete}',
                'urlCreator' => function ($action, ChapterPublication $model, $key, $index) {
                    if ($action == 'publish') {
                        $url = Url::to(['/chapter/publish', 'chapter_id' => $model->chapter_id,
                            'service_id' => $model->service_id]);
                    } else {
                        $url = Url::to(['/chapter/publication-setting-' . $action, 'id' => $model->id]);
                    }
                    return $url;
                },
                'buttons' => [
                    'publish' => function ($url, $model) {
                        return Html::a('<span class="fas fa-upload"></span>', $url, [
                            'title' => Yii::t('app', 'Publish'),
                            'data' => ['method' => 'post'],
                        ]);

                    }
                ],
            ],
        ],
    ]); ?>
</div>
