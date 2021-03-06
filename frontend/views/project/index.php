<?php

use common\models\project\Project;
use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel common\models\project\ProjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Projects');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-index">

    <p>
        <?= Html::a('<i class="fas fa-plus"></i> ' . Yii::t('app', 'Create Project'),
            ['create'], ['class' => 'btn btn-sm btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'resizableColumns' => false,
        'columns' => [
            [
                'attribute' => 'name',
                'value' => function (Project $model) {
                    return Html::a($model->name, ['/project/view', 'id' => $model->id]);
                },
                'format' => 'raw',
            ],
            'word_count:integer',
            'edit_count:integer',
            'updated_at:datetime',
            [
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{update} {delete}',
            ],
        ],
    ]); ?>


</div>
