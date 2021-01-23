<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\project\Project */
/* @var $sourceSearchModel common\models\project\SourceSearch */
/* @var $sourceDataProvider yii\data\ActiveDataProvider */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="project-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php if (!$model->accessToken) {
            echo Html::a(Yii::t('app', 'Create Access Token'),
                ['create-access-token', 'project_id' => $model->id], ['class' => 'btn btn-info']);
        } ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= $this->render('/source/_index', [
        'project' => $model,
        'dataProvider' => $sourceDataProvider,
        'searchModel' => $sourceSearchModel,
    ]) ?>

</div>
