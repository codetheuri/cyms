<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model admin\models\static\Email */
/* @var $form yii\bootstrap5\ActiveForm */

$this->title = 'Email Settings';
?>

<div class="card-body w-100">
    
    <?php $form = ActiveForm::begin([
        'id' => 'email-settings-form',
        'options' => ['class' => 'w-100'],
        'fieldConfig' => [
            // This wrapper class matches your original template style
            'options' => ['class' => 'input-block mb-3'], 
            'inputOptions' => ['class' => 'form-control'],
        ],
    ]); ?>

    <div class="row">
        <div class="col-lg-4 col-12">
            <?= $form->field($model, 'sender_name')->textInput(['placeholder' => 'Enter Sender Name']) ?>
        </div>

        <div class="col-lg-4 col-12">
            <?= $form->field($model, 'sender_email')->textInput(['placeholder' => 'Enter Sender Email']) ?>
        </div>
         <div class="col-lg-4 col-12">
            <?= $form->field($model, 'admin_email')->textInput(['placeholder' => 'Enter Admin Email']) ?>
        </div>

        <div class="col-lg-6 col-12">
            <?= $form->field($model, 'smtp_host')->textInput(['placeholder' => 'e.g. mail.domain.com']) ?>
        </div>

        <div class="col-lg-3 col-6">
            <?= $form->field($model, 'smtp_port')->textInput(['placeholder' => 'e.g. 587']) ?>
        </div>

        <div class="col-lg-3 col-6">
            <?= $form->field($model, 'email_encryption')->dropDownList([
                'tls' => 'TLS', 
                'ssl' => 'SSL',
                '' => 'None'
            ], ['class' => 'form-select']) ?>
        </div>

        <div class="col-lg-6 col-12">
            <?= $form->field($model, 'smtp_user')->textInput(['placeholder' => 'Enter SMTP Username']) ?>
        </div>

        <div class="col-lg-6 col-12">
            <?= $form->field($model, 'smtp_password')->passwordInput(['placeholder' => 'Enter SMTP Password']) ?>
        </div>

        <div class="col-lg-12">
            <div class="btn-path text-end">
                <?= Html::submitButton('Save Changes', ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>