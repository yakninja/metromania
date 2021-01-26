<?php

use common\models\project\ChapterExport;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\project\Chapter */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Chapter Export Settings');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->project->name, 'url' => ['/project/view', 'id' => $model->project->id]];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['/chapter/view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="chapter-export-settings">
    <p>
        <?= Html::a('<i class="fas fa-plus"></i> ' . Yii::t('app', 'Add Export Setting'),
            ['create-export-setting', 'chapter_id' => $model->id],
            ['class' => 'btn btn-sm btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'resizableColumns' => false,
        'columns' => [
            [
                'attribute' => 'provider.name',
                'label' => Yii::t('app', 'Export Provider'),
            ],
            'url:url',
            [
                'attribute' => 'status',
                'value' => function (ChapterExport $model) {
                    $value = ChapterExport::statusLabels()[$model->status];
                    if ($model->status == ChapterExport::STATUS_ERROR) {
                        $value = Html::tag('span', $value,
                            ['class' => 'badge badge-danger', 'title' => $model->error_message]);
                    }
                    return $value;
                },
                'filter' => ChapterExport::statusLabels(),
                'format' => 'raw',
            ],
            'updated_at:datetime',
            [
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{update} {delete}',
                'urlCreator' => function ($action, $model, $key, $index) {
                    $url = Url::to(['/project/export-setting-' . $action, 'id' => $model->id]);
                    return $url;
                }
            ],
        ],
    ]); ?>
</div>
