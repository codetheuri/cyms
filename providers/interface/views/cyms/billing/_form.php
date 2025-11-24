<?php

use helpers\Html;
use helpers\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var dashboard\models\BillingRecords $model */
/** @var helpers\widgets\ActiveForm $form */
?>

<div class="billing-records-form">
    <?php $form = ActiveForm::begin(['options' => ['data-pjax' => true]]);?>
    <div class="row">
        <div class="col-md-12">
          <?= $form->field($model, 'visit_id')->textInput() ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'storage_days')->textInput() ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'tariff_rate')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'grand_total')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'status')->dropDownList([ 'PENDING' => 'PENDING', 'PAID' => 'PAID', 'CANCELLED' => 'CANCELLED', ], ['prompt' => '']) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'receipt_no')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'created_at')->textInput() ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'updated_at')->textInput() ?>
        </div>
    </div>
    <div class="block-content block-content-full text-center">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
