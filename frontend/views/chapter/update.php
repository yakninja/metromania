<?php

/* @var $this yii\web\View */
/* @var $model common\models\project\Chapter */

$this->title = Yii::t('app', 'Update Chapter');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['/project/index']];
$this->params['breadcrumbs'][] = ['label' => $model->project->name,
    'url' => ['/project/view', 'id' => $model->project_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="chapter-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
