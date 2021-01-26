<?php

use frontend\models\ChapterImportForm;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model ChapterImportForm */

$this->title = Yii::t('app', 'Import Chapters');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['/project/index']];
$this->params['breadcrumbs'][] = ['label' => $model->project->name,
    'url' => ['/project/view', 'id' => $model->project_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="chapter-create">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'urls')->textarea(['rows' => 10])
        ->label(false)
        ->hint(Yii::t('app', 'One URL per line')) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save"></i> ' . Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
