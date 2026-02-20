<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;

/* @var clone yii\base\DynamicModel $model */
/* @var auth\models\User $user */
?>

<?php $form = ActiveForm::begin(['id' => 'admin-reset-password-form']); ?>

<div class="alert alert-warning fs-sm mb-4">
    <i class="fa fa-exclamation-triangle me-1"></i> You are forcing a password reset for <strong><?= Html::encode($user->username) ?></strong>. This will log them out of any active sessions.
</div>

<div class="mb-3">
    <?= $form->field($model, 'new_password')->passwordInput(['class' => 'form-control form-control-lg', 'placeholder' => 'Enter new password'])->label('New Password', ['class' => 'fw-bold text-muted fs-sm text-uppercase']) ?>
</div>

<div class="mb-4">
    <?= $form->field($model, 'confirm_password')->passwordInput(['class' => 'form-control form-control-lg', 'placeholder' => 'Confirm new password'])->label('Confirm Password', ['class' => 'fw-bold text-muted fs-sm text-uppercase']) ?>
</div>

<div class="text-end border-top pt-3">
    <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
    <?= Html::submitButton('<i class="fa fa-save me-1"></i> Reset Password', ['class' => 'btn btn-primary fw-bold px-4']) ?>
</div>

<?php ActiveForm::end(); ?>