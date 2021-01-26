<?php

use common\models\project\Chapter;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\project\Chapter */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['/project/index']];
$this->params['breadcrumbs'][] = ['label' => $model->project->name,
    'url' => ['/project/view', 'id' => $model->project_id]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="chapter-view">

    <p>
        <?= Html::a('<i class="fas fa-angle-double-down"></i> ' . Yii::t('app', 'Get Chapter'),
            ['get', 'id' => $model->id], [
            'class' => 'btn btn-sm btn-info',
            'data' => [
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('<i class="fas fa-pen"></i> ' . Yii::t('app', 'Update'),
            ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary']) ?>
        <?= Html::a('<i class="fas fa-cogs"></i> ' . Yii::t('app', 'Chapter Export Settings'),
            ['export-settings', 'chapter_id' => $model->id], ['class' => 'btn btn-sm btn-info']) ?>
        <?= Html::a('<i class="fas fa-trash"></i> ' . Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-sm btn-danger float-right',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'project_id',
                'value' => function (Chapter $model) {
                    return Html::a($model->project->name, ['/project/view', 'id' => $model->project_id]);
                },
                'format' => 'raw',
            ],
            'url:url',
            'title',
            'created_at:datetime',
            'updated_at:datetime',
            [
                'attribute' => 'locked_until',
                'value' => function (Chapter $model) {
                    $f = Yii::$app->formatter;
                    return $model->locked_until ?
                        $f->asDatetime($model->locked_until) :
                        $f->nullDisplay;
                },
                'format' => 'raw',
            ],
            'priority',
            [
                'attribute' => 'status',
                'value' => function (Chapter $model) {
                    return Chapter::statusLabels()[$model->status];
                }
            ],
            'word_count:integer',
            'edit_count:integer',
            'error_message:ntext',
        ],
    ]) ?>

    <?php foreach ($model->paragraphs as $p): ?>
        <p><?= Html::encode($p->content) ?><code>¶</code></p>
    <?php endforeach ?>

</div>
