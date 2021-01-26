<?php

/* @var $this yii\web\View */
/* @var $project common\models\project\Project */
/* @var $model \common\models\project\ChapterPublication */
/* @var $authUrl string */

$this->title = Yii::t('app', 'Update Publication Setting');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->chapter->project->name, 'url' => ['view', 'id' => $model->chapter->project->id]];
$this->params['breadcrumbs'][] = ['label' => $model->chapter->title, 'url' => ['/chapter/view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Publication Settings'),
    'url' => ['/chapter/publication-settings', 'chapter_id' => $model->chapter_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-update-publication-setting">
    <?= $this->render('_publication_setting_form', ['model' => $model]) ?>
</div>
