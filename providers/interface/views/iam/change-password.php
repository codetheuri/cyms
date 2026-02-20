<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;

/* @var clone \auth\models\static\ChangePassword $model */
?>

<?php $form = ActiveForm::begin(['id' => 'self-change-password-form']); ?>

<div class="alert alert-info fs-sm mb-4">
    <i class="fa fa-info-circle me-1"></i> Update your account password. You will be logged out and required to log in again after saving.
</div>

<div class="mb-3">
    <?= $form->field($model, 'currentPassword')->passwordInput([
        'class' => 'form-control form-control-lg', 
        'placeholder' => 'Enter current password'
    ])->label('Current Password', ['class' => 'fw-bold text-muted fs-sm text-uppercase']) ?>
</div>

<div class="mb-3">
    <?= $form->field($model, 'newPassword')->passwordInput([
        'class' => 'form-control form-control-lg', 
        'placeholder' => 'Enter new password'
    ])->label('New Password', ['class' => 'fw-bold text-muted fs-sm text-uppercase']) ?>
</div>

<div class="mb-4">
    <?= $form->field($model, 'confirmPassword')->passwordInput([
        'class' => 'form-control form-control-lg', 
        'placeholder' => 'Confirm new password'
    ])->label('Confirm Password', ['class' => 'fw-bold text-muted fs-sm text-uppercase']) ?>
</div>

<div class="text-end border-top pt-3">
    <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
    <?= Html::submitButton('<i class="fa fa-save me-1"></i> Update My Password', ['class' => 'btn btn-primary fw-bold px-4']) ?>
</div>

<?php ActiveForm::end(); ?>