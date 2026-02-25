<?php
use helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var admin\models\static\Tariff $model */

$this->title = 'Tariff & Billing Settings';
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-body-light border-bottom-0 pt-4 pb-3">
        <h5 class="card-title mb-0 fw-bold"><i class="fa fa-file-invoice-dollar text-primary me-2"></i> Manage Billing Rates</h5>
        <p class="text-muted fs-sm mb-0 mt-1">Configure the default daily storage and lift charges in both Base (KES) and Foreign (USD) currencies.</p>
    </div>
    
    <div class="card-body p-4">
        
        <?php $form = ActiveForm::begin(['errorCssClass' => 'is-invalid', 'successCssClass' => 'is-valid']); ?>

        <div class="p-3 bg-body-extra-light rounded border mb-4">
            <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="fa fa-flag me-1"></i> Base Currency (Local)</h6>
            <div class="row g-3">
                <div class="col-md-3">
                    <?= $form->field($model, 'currency_code')->textInput([
                        'class' => 'form-control text-uppercase fw-bold text-primary bg-light', 
                        'readonly' => true
                    ])->label('Base Currency') ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Storage Rate/Day</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted fw-bold">KES</span>
                        <?= $form->field($model, 'storage_rate_per_day', ['options'=>['tag'=>false]])->textInput(['type' => 'number', 'step' => '0.01', 'class'=>'form-control'])->label(false) ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Lift ON Charge</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted fw-bold">KES</span>
                        <?= $form->field($model, 'lift_on_charges', ['options'=>['tag'=>false]])->textInput(['type' => 'number', 'step' => '0.01', 'class'=>'form-control'])->label(false) ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Lift OFF Charge</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted fw-bold">KES</span>
                        <?= $form->field($model, 'lift_off_charges', ['options'=>['tag'=>false]])->textInput(['type' => 'number', 'step' => '0.01', 'class'=>'form-control'])->label(false) ?>
                    </div>
                </div>
            </div>
        </div>

       

        <div class="p-3 bg-body-extra-light rounded border mb-4">
            <h6 class="text-dark fw-bold border-bottom pb-2 mb-3"><i class="fa fa-percent me-1"></i> Taxes & Fees</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Value Added Tax (VAT)</label>
                    <div class="input-group">
                        <?= $form->field($model, 'tax_percentage', ['options'=>['tag'=>false]])->textInput(['type' => 'number', 'step' => '0.01', 'class'=>'form-control'])->label(false) ?>
                        <span class="input-group-text bg-dark text-white fw-bold">%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-3 border-top text-end">
            <?= Html::submitButton('<i class="fa fa-save me-1"></i> Save Tariff Settings', ['class' => 'btn btn-primary btn-lg fw-bold px-5']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>