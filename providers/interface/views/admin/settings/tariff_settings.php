<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use admin\models\static\Tariff;

/** @var yii\web\View $this */
/** @var Tariff $model */

$this->title = Html::encode('Tariff & Billing Settings');
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Manage Billing Rates</h5>
    </div>
    <div class="card-body">
        
        <?php $form = ActiveForm::begin([
            'fieldConfig' => [
                'template' => "<div class='input-block mb-3'>{label}\n{input}\n{hint}\n{error}</div>",
            ]
        ]); ?>

        <div class="row">
            <div class="col-12 mb-3">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle me-2"></i> These rates will be used to automatically calculate bills for all containers exiting the yard.
                </div>
            </div>

            <?php foreach (Tariff::layout() as $attribute => $class): ?>
                <div class="<?= $class ?>">
                    <?php 
                        // Add specific input types/placeholders based on attribute
                        if ($attribute === 'currency_code') {
                            echo $form->field($model, $attribute)->textInput(['placeholder' => 'KES', 'class' => 'form-control text-uppercase']);
                        } elseif ($attribute === 'tax_percentage') {
                            echo $form->field($model, $attribute)->textInput(['type' => 'number', 'step' => '0.01', 'placeholder' => '16']);
                        } else {
                            echo $form->field($model, $attribute)->textInput(['type' => 'number', 'min' => '0', 'step' => '0.01', 'placeholder' => '0.00']);
                        }
                    ?>
                </div>
            <?php endforeach; ?>

            <div class="col-lg-12 pt-4 border-top">
                <div class="btn-path text-end">
                    <?= Html::submitButton('<i class="fa fa-save me-1"></i> Save Tariffs', ['class' => 'btn btn-primary btn-lg']) ?>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>