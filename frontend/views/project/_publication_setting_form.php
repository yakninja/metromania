<?php

/* @var $this yii\web\View */
/* @var $model \common\models\project\ProjectPublicationSettings */

/* @var $form kartik\widgets\ActiveForm */

use common\models\PublicationService;
use kartik\widgets\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;

?>

<div class="publication-setting-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'service_id')
        ->dropDownList(ArrayHelper::map(PublicationService::find()->all(), 'id', 'name')) ?>
    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'password')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save"></i> ' . Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
