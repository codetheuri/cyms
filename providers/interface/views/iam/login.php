<?php

use helpers\Html;
use helpers\widgets\ActiveForm;

/** @var yii\web\View $this */

$this->title = 'Sign In';
?>

<div class="row justify-content-center push">
    <div class="col-md-8 col-lg-6 col-xl-4">
        <div class="block block-rounded mb-0 shadow-lg overflow-hidden">
            
            <div class="block-header block-header-default bg-body-extra-light text-center py-4">
                <div class="w-100">
                    <div class="mb-2">
                        <i class="fa fa-truck-fast fa-2x text-primary"></i>
                    </div>
                    <h1 class="h4 mb-1 fw-bold"><?= Yii::$app->name ?></h1>
                    <p class="fw-medium text-muted mb-0">
                        Welcome, please login.
                    </p>
                </div>
            </div>

            <div class="block-content block-content-full px-lg-5 py-md-4 bg-body-light">
                <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                    
                    <div class="mb-4">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">
                                <i class="fa fa-user-circle"></i>
                            </span>
                            <?= $form->field($model, 'username', [
                                'template' => "{input}\n{error}",
                                'options' => ['class' => 'flex-grow-1'] 
                            ])->textInput([
                                'autofocus' => true, 
                                'class' => 'form-control form-control-alt', 
                                'placeholder' => 'Username'
                            ])->label(false) ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">
                                <i class="fa fa-asterisk"></i>
                            </span>
                            <?= $form->field($model, 'password', [
                                'template' => "{input}\n{error}",
                                'options' => ['class' => 'flex-grow-1']
                            ])->passwordInput([
                                'class' => 'form-control form-control-alt', 
                                'placeholder' => 'Password'
                            ])->label(false) ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <?= $form->field($model, 'rememberMe')->checkbox([
                                'class' => 'form-check-input',
                                'template' => "<div class=\"form-check\">{input} {label}</div>",
                            ]) ?>
                        </div>
                        <div>
                            <a class="fs-sm fw-medium text-muted" href="site/forgot-password">Forgot Password?</a>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12">
                            <?= Html::submitButton(
                                '<i class="fa fa-fw fa-sign-in-alt me-1 opacity-50"></i> Sign In', 
                                ['class' => 'btn w-100 btn-primary btn-lg text-white']
                            ) ?>
                        </div>
                    </div>

                <?php ActiveForm::end(); ?>
            </div>
            
            <div class="block-content block-content-full bg-body-extra-light text-center py-3 fs-sm">
                <span class="text-muted">&copy; <?= date('Y') ?> <?= Yii::$app->name ?></span>
            </div>

        </div>
        </div>
</div>