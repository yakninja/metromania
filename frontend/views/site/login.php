<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

/* @var $model \common\models\LoginForm */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Login');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Yii::t('app', 'Please fill out the following fields to login:') ?></p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

            <?= $form->field($model, 'password')->passwordInput() ?>

            <?= $form->field($model, 'rememberMe')->checkbox() ?>

            <div style="color:#999;margin:1em 0">
                <?= Yii::t('app', 'If you forgot your password you can {reset_it}',
                    ['reset_it' => Html::a(Yii::t('app', 'reset it'), ['site/request-password-reset'])]) ?>
                <br>
                <?= Yii::t('app', 'Need new verification email? {Resend}',
                    ['Resend' => Html::a(Yii::t('app', 'Resend'), ['site/resend-verification-email'])]) ?>
            </div>

            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
