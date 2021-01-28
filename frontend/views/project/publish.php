<?php

use common\models\project\PublicationService;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \frontend\models\ProjectPublicationForm */
/** @var array $chapterStats */

$this->title = Yii::t('app', 'Publish Project');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->project->name, 'url' => ['view', 'id' => $model->project->id]];
$this->params['breadcrumbs'][] = $this->title;

$providers = ArrayHelper::map(PublicationService::find()->all(), 'id',
    function (PublicationService $model) use ($chapterStats) {
        $value = $model->name;
        if ($count = $chapterStats['having_service'][$model->id]) {
            $value .= " ($count)";
        }
        return $value;
    });
$disabledServices = [];
foreach ($chapterStats['having_service'] as $id => $count) {
    if (!$count) $disabledServices[] = $id;
}

?>
<div class="project-publish">
    <?= $this->render('_publication_description', ['model' => $model, 'chapterStats' => $chapterStats]) ?>

    <?= Html::errorSummary($model) ?>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'service_id')
        ->checkboxList($providers, ['disabledItems' => $disabledServices]) ?>

    <?= $form->field($model, 'not_having_edits')->checkbox()
        ->hint(Yii::t('app', '{n} out of {total} chapters',
            ['n' => $chapterStats['total'] - $chapterStats['with_edits'], 'total' => $chapterStats['total']])) ?>

    <?= $form->field($model, 'only_if_changed')->checkbox()
        ->hint(Yii::t('app', '{n} out of {total} tasks',
            ['n' => $chapterStats['changed_content'], 'total' => $chapterStats['total_publications']])) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-upload"></i> ' . Yii::t('app', 'Publish'),
            ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
