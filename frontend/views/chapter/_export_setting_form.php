<?php

/* @var $this yii\web\View */
/* @var $model \common\models\project\ChapterExport */

/* @var $form kartik\widgets\ActiveForm */

use common\models\project\ExportProvider;
use kartik\widgets\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;

?>

<div class="export-setting-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'provider_id')
        ->dropDownList(ArrayHelper::map(ExportProvider::find()->all(), 'id', 'name')) ?>
    <?= $form->field($model, 'url')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save"></i> ' . Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
