<?php

/* @var $this yii\web\View */
/* @var $model common\models\project\Source */
/* @var $form yii\widgets\ActiveForm */

use kartik\widgets\ActiveForm;
use yii\bootstrap4\Html;

?>

<div class="source-form">

    <?php $form = ActiveForm::begin(); ?>
<?= Html::errorSummary($model) ?>
    <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
