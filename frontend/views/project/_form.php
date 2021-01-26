<?php

/* @var $this yii\web\View */
/* @var $model common\models\project\Project */
/* @var $form yii\widgets\ActiveForm */

use kartik\widgets\ActiveForm;
use yii\bootstrap4\Html;

?>

<div class="project-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save"></i> ' . Yii::t('app', 'Save'),
            ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
