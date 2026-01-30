<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Add Yard Slot';
?>

<div class="block block-rounded content-card">
    <div class="block-header block-header-default">
        <h3 class="block-title"><?= Html::encode($this->title) ?></h3>
    </div>
    <div class="block-content block-content-full">
        <?php $form = ActiveForm::begin(); ?>
        
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'block')->textInput(['maxlength' => true, 'placeholder' => 'A']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'row')->textInput(['type' => 'number']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'bay')->textInput(['type' => 'number']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'slot_name')->textInput(['placeholder' => 'A-01-01']) ?>
                <div class="form-text">Unique name for this slot.</div>
            </div>
        </div>

        <div class="pt-3">
            <?= Html::submitButton('Save Slot', ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>