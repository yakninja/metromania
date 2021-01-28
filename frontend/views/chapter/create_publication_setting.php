<?php

/* @var $this yii\web\View */
/* @var $model \common\models\chapter\ChapterPublication */
/* @var $authUrl string */

$this->title = Yii::t('app', 'Add Publication Setting');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->chapter->project->name,
    'url' => ['/project/view', 'id' => $model->chapter->project_id]];
$this->params['breadcrumbs'][] = ['label' => $model->chapter->title,
    'url' => ['/chapter/view', 'id' => $model->chapter_id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Publication Settings'),
    'url' => ['publication-settings', 'chapter_id' => $model->chapter_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-create-publication-setting">
    <?= $this->render('_publication_setting_form', ['model' => $model]) ?>
</div>
