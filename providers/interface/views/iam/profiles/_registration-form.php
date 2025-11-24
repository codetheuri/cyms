<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use helpers\widgets\ActiveForm;

?>
<?php Pjax::begin() ?>
<div class="auth-item-form">
  <?php $form = ActiveForm::begin(['options' => ['data-pjax' => true]]); ?>
  <div class="row">
    <div class="col-sm-12">
      <?= $form->field($model, 'username')->textInput(['maxlength' => 64]) ?>
    </div>
    <div class="col-sm-12">
      <?= $form->field($model, 'password')->passwordInput(['maxlength' => 64]) ?>
    </div>
    <div class="col-sm-12">
      <?= $form->field($model, 'confirm_password')->passwordInput() ?>
    </div>
  </div>
  <div class="block-content block-content-full text-center">
    <?= Html::submitButton('Create User', ['class' => 'btn btn-primary','name' => 'submit-button']) ?>
  </div>
  <?php ActiveForm::end(); ?>
</div>
<?php Pjax::end() ?>