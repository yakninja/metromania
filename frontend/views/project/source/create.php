<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $project common\models\project\Project */
/* @var $model common\models\project\Source */

$this->title = Yii::t('app', 'Create Source');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $project->name, 'url' => ['view', 'id' => $project->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="source-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
