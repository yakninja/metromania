<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $project common\models\project\Project */
/* @var $model \frontend\models\GoogleAuthForm */
/* @var $authUrl string */

$this->title = Yii::t('app', 'Create Access Token');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $project->name, 'url' => ['view', 'id' => $project->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-create-access-token">

    <ol>
        <li><?= Html::a(Yii::t('app', 'Visit this link'), $authUrl, ['target' => '_blank']) ?></li>
        <li><?= Yii::t('app', 'Enter the code in the field below') ?></li>
    </ol>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'authCode')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save"></i> ' . Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
