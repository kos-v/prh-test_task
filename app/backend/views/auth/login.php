<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $activeForm */
/** @var \backend\forms\LoginForm $form */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = Yii::t('app/auth', 'Authentication');
?>
<div class="site-login">
    <div class="mt-5 offset-lg-3 col-lg-6">
        <h1><?= Html::encode($this->title) ?></h1>

        <p><?= Yii::t('app/auth','Please fill out the following fields to login') ?>:</p>

        <?php $activeForm = ActiveForm::begin(['id' => 'login-form']); ?>

            <?= $activeForm->field($form, 'username')->textInput(['autofocus' => true]) ?>

            <?= $activeForm->field($form, 'password')->passwordInput() ?>

            <?= $activeForm->field($form, 'rememberMe')->checkbox() ?>

            <div class="form-group">
                <?= Html::submitButton(Yii::t('app/auth', 'Login'), ['class' => 'btn btn-primary btn-block', 'name' => 'login-button']) ?>
            </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
