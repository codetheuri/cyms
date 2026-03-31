<?php

use helpers\widgets\ActiveForm;
use helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var dashboard\models\ExpenseCategories $model */
?>

<div class="category-entry-form">
    <?php Pjax::begin(['id' => 'pjax-category-form', 'enablePushState' => false, 'timeout' => 5000]); ?>
    <?php $form = ActiveForm::begin([
        'id' => 'category-entry-form',
        'enableAjaxValidation' => false,
        'options' => ['data-pjax' => true],
    ]); ?>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'category_name')->textInput([
                'class' => 'form-control form-control-lg fw-bold',
                'placeholder' => 'e.g. Fuel, Staff, Electricity...'
            ]) ?>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <?= $form->field($model, 'description')->textarea(['rows' => 3, 'placeholder' => 'Optional...']) ?>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <?= $form->field($model, 'status')->dropDownList([
                10 => 'Active (Visible for expenses)',
                9 => 'Inactive (Hidden)',
            ], ['class' => 'form-select']) ?>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-alt-success px-4 fw-bold">
                <i class="fa fa-save me-1"></i> Update System Categories
            </button>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    <?php Pjax::end(); ?>
</div>
