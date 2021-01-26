<?php

use common\models\project\ChapterPublication;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\project\Chapter */
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
                'attribute' => 'provider.name',
                'label' => Yii::t('app', 'Publication Provider'),
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
            'updated_at:datetime',
            [
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{update} {delete}',
                'urlCreator' => function ($action, $model, $key, $index) {
                    $url = Url::to(['/chapter/publication-setting-' . $action, 'id' => $model->id]);
                    return $url;
                }
            ],
        ],
    ]); ?>
</div>
