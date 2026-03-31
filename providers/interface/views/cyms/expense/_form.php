<?php

use helpers\widgets\ActiveForm;
use helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var dashboard\models\Expenses $model */
/** @var array $categories */
?>

<div class="expenses-form">
    <?php Pjax::begin(['id' => 'pjax-expense-form', 'enablePushState' => false, 'timeout' => 5000]); ?>
    <?php $form = ActiveForm::begin([
        'id' => 'expense-entry-form',
        'enableAjaxValidation' => false,
        'options' => ['data-pjax' => true],
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'category_id')->dropDownList($categories, [
                'prompt' => '-- Select Category --',
                'class' => 'form-select form-select-lg fw-bold border-primary'
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'amount')->textInput([
                'type' => 'number', 
                'step' => '0.01', 
                'class' => 'form-control form-control-lg fw-bold text-danger',
                'placeholder' => '0.00'
            ]) ?>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <?= $form->field($model, 'expense_date')->textInput([
                'type' => 'date', 
                'class' => 'form-control'
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'payment_method')->dropDownList([
                'CASH' => 'CASH / PETTY CASH',
                'BANK' => 'BANK TRANSFER',
                'MPESA' => 'MPESA / MOBILE MONEY',
                'CHEQUE' => 'CHEQUE',
            ], ['class' => 'form-select']) ?>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <?= $form->field($model, 'reference_no')->textInput(['placeholder' => 'Receipt #, Voucher #, etc.']) ?>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <?= $form->field($model, 'description')->textarea(['rows' => 3, 'placeholder' => 'Breakdown of what this money was used for...']) ?>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-alt-success px-4 fw-bold">
                <i class="fa fa-save me-1"></i> Save Financial Record
            </button>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    <?php Pjax::end(); ?>
</div>
