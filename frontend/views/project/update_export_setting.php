<?php

/* @var $this yii\web\View */
/* @var $project common\models\project\Project */
/* @var $model \common\models\project\ProjectExportSettings */
/* @var $authUrl string */

$this->title = Yii::t('app', 'Update Export Setting');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->project->name, 'url' => ['view', 'id' => $model->project->id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Export Settings'),
    'url' => ['export-settings', 'project_id' => $model->project->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-update-export-setting">
    <?= $this->render('_export_setting_form', ['model' => $model, 'project' => $model->project]) ?>
</div>
