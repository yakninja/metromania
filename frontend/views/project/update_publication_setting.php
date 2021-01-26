<?php

/* @var $this yii\web\View */
/* @var $project common\models\project\Project */
/* @var $model \common\models\project\ProjectPublicationSettings */
/* @var $authUrl string */

$this->title = Yii::t('app', 'Update Publication Setting');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->project->name, 'url' => ['view', 'id' => $model->project->id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Publication Settings'),
    'url' => ['publication-settings', 'project_id' => $model->project->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-update-publication-setting">
    <?= $this->render('_publication_setting_form', ['model' => $model, 'project' => $model->project]) ?>
</div>
