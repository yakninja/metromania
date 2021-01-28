<?php

use common\models\project\PublicationService;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \frontend\models\ProjectPublicationForm */

$this->title = Yii::t('app', 'Publish Project');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->project->name, 'url' => ['view', 'id' => $model->project->id]];
$this->params['breadcrumbs'][] = $this->title;

$providers = ArrayHelper::map(PublicationService::find()->all(), 'id', 'name');

?>
<div class="project-publish">
    <p><?= $this->render('_publication_description', ['model' => $model]) ?></p>

    <?= Html::errorSummary($model) ?>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'service_id')->checkboxList($providers) ?>

    <?= $form->field($model, 'not_having_edits')->checkbox() ?>
    <?= $form->field($model, 'only_if_changed')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-upload"></i> ' . Yii::t('app', 'Publish'),
            ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
