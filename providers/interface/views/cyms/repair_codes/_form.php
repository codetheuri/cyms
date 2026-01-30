<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="master-repair-codes-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4 mb-3">
            <?= $form->field($model, 'repair_code')->textInput(['maxlength' => true, 'placeholder' => 'e.g. PN040']) ?>
            <div class="form-text">Unique code for this repair type.</div>
        </div>
        
        <div class="col-md-8 mb-3">
            <?= $form->field($model, 'description')->textInput(['maxlength' => true, 'placeholder' => 'e.g. LEFT SIDE PANEL DENTED']) ?>
        </div>
    </div>

    <h5 class="border-bottom pb-2 mb-3 text-muted mt-3">Standard Costs & Time</h5>
    
    <div class="row">
        <div class="col-md-4 mb-3">
            <?= $form->field($model, 'standard_hours')->textInput(['type' => 'number', 'step' => '0.01']) ?>
        </div>
        <div class="col-md-4 mb-3">
            <?= $form->field($model, 'labor_cost')->textInput(['type' => 'number', 'step' => '0.01']) ?>
            <div class="form-text">Fixed labor cost (if applicable).</div>
        </div>
        <div class="col-md-4 mb-3">
            <?= $form->field($model, 'material_cost')->textInput(['type' => 'number', 'step' => '0.01']) ?>
        </div>
    </div>

    <div class="form-group pt-4 border-top">
        <?= Html::submitButton('<i class="fa fa-save"></i> Save Repair Code', ['class' => 'btn btn-primary btn-lg']) ?>
        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-alt-secondary btn-lg']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>