<?php

use common\models\chapter\Chapter;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\project\Project */
/* @var $chapterSearchModel common\models\chapter\ChapterSearch */
/* @var $chapterDataProvider yii\data\ActiveDataProvider */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$stats = $model->getChapterStats();
$warnings = [];
if ($n = @$stats[Chapter::STATUS_ERROR]) {
    $warnings[] = Yii::t('app', '{n} chapters {with_errors}', ['n' => $n,
        'with_errors' => Html::a(Yii::t('app', 'with errors'),
            ['view', 'id' => $model->id, 'ChapterSearch[status]' => Chapter::STATUS_ERROR])]);
}
if ($n = $stats['warnings']) {
    $warnings[] = Yii::t('app', '{n} chapters {with_warnings}', ['n' => $n,
        'with_warnings' => Html::a(Yii::t('app', 'with warnings'),
            ['view', 'id' => $model->id, 'ChapterSearch[status]' => 'warnings'])]);
}
?>
<div class="project-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($warnings) echo '<p>' . implode(', ', $warnings) . '</p>' ?>

    <p>
        <?= Html::a('<i class="fas fa-pen"></i> ' . Yii::t('app', 'Update'),
            ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary']) ?>
        <?php
        if (!$model->accessToken) {
            echo Html::a('<i class="fas fa-plus"></i> ' . Yii::t('app', 'Create Access Token'),
                ['create-access-token', 'project_id' => $model->id], ['class' => 'btn btn-sm btn-info']);
        } else {
            echo Html::a('<i class="fas fa-angle-double-down"></i> ' . Yii::t('app', 'Get all chapters'),
                ['get-all-chapters', 'project_id' => $model->id], [
                    'class' => 'btn btn-info btn-sm',
                    'data' => [
                        'method' => 'post',
                    ],
                ]);
        }
        ?>
        <?= Html::a('<i class="fas fa-cogs"></i> ' . Yii::t('app', 'Project Publication Settings'),
            ['publication-settings', 'project_id' => $model->id], ['class' => 'btn btn-sm btn-info']) ?>

        <?php if ($model->publicationSettings): ?>
            <?= Html::a('<i class="fas fa-upload"></i> ' . Yii::t('app', 'Publish...'),
                ['publish', 'id' => $model->id], ['class' => 'btn btn-sm btn-info']) ?>
        <?php endif ?>

        <?= Html::a('<i class="fas fa-trash"></i> ' . Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-sm btn-danger float-right',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= $this->render('/chapter/_index', [
        'project' => $model,
        'dataProvider' => $chapterDataProvider,
        'searchModel' => $chapterSearchModel,
    ]) ?>

</div>
