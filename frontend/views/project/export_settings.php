<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $project common\models\project\Project */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Project Export Settings');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Projects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $project->name, 'url' => ['view', 'id' => $project->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-export-settings">
    <p>
        <?= Html::a('<i class="fas fa-plus"></i> ' . Yii::t('app', 'Add Export Setting'),
            ['create-export-setting', 'project_id' => $project->id],
            ['class' => 'btn btn-sm btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'resizableColumns' => false,
        'columns' => [
            [
                'attribute' => 'provider.name',
                'label' => Yii::t('app', 'Export Provider'),
            ],
            'username',
            [
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{update} {delete}',
                'urlCreator' => function ($action, $model, $key, $index) {
                    $url = Url::to(['/project/export-setting-' . $action, 'id' => $model->id]);
                    return $url;
                }
            ],
        ],
    ]); ?>
</div>
