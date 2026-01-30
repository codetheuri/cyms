<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model dashboard\models\MasterContainerTypes */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="master-container-types-form">

    <?php $form = ActiveForm::begin(); ?>

    <h5 class="border-bottom pb-2 mb-3 text-muted">Technical Specs</h5>
    <div class="row">
        <div class="col-md-4 mb-3">
            <?= $form->field($model, 'iso_code')->textInput(['placeholder' => 'e.g. 45G1', 'maxlength' => true]) ?>
            <div class="form-text">Standard ISO Code (Unique).</div>
        </div>
        <div class="col-md-4 mb-3">
            <?= $form->field($model, 'size')->dropDownList([
                '20' => '20 ft',
                '40' => '40 ft',
                '45' => '45 ft',
            ], ['prompt' => 'Select Size...']) ?>
        </div>
        <div class="col-md-4 mb-3">
            <?= $form->field($model, 'type_group')->dropDownList([
                'GP' => 'General Purpose (GP)',
                'HC' => 'High Cube (HC)',
                'OT' => 'Open Top (OT)',
                'RF' => 'Reefer (RF)',
                'FR' => 'Flat Rack (FR)',
            ], ['prompt' => 'Select Group...']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-3">
            <?= $form->field($model, 'description')->textInput(['placeholder' => 'e.g. 40ft High Cube Container']) ?>
        </div>
    </div>

    <h5 class="border-bottom pb-2 mb-3 mt-3 text-success">Billing Configuration</h5>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Daily Storage Rate (KES)</label>
            <div class="input-group">
                <span class="input-group-text">KES</span>
                <?= $form->field($model, 'daily_rate', ['options' => ['tag' => false]])->textInput([
                    'type' => 'number', 
                    'step' => '0.01', 
                    'placeholder' => '0.00',
                    'class' => 'form-control form-control-lg fw-bold'
                ])->label(false) ?>
            </div>
            <div class="form-text">This amount will be charged automatically per day for this container type.</div>
        </div>
    </div>

    <div class="pt-4 border-top d-flex justify-content-between">
        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-lg btn-alt-secondary']) ?>
        <?= Html::submitButton('<i class="fa fa-save me-1"></i> Save Settings', ['class' => 'btn btn-lg btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>