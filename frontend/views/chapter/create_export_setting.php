<?php

/* @var $this yii\web\View */
/* @var $model \common\models\project\ChapterExport */
/* @var $authUrl string */

$this->title = Yii::t('app', 'Add Export Setting');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->chapter->project->name,
    'url' => ['/project/view', 'id' => $model->chapter->project_id]];
$this->params['breadcrumbs'][] = ['label' => $model->chapter->title,
    'url' => ['/chapter/view', 'id' => $model->chapter_id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Export Settings'),
    'url' => ['export-settings', 'chapter_id' => $model->chapter_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-create-export-setting">
    <?= $this->render('_export_setting_form', ['model' => $model]) ?>
</div>
