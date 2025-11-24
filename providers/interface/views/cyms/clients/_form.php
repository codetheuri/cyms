<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model dashboard\models\MasterContainerOwners */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="master-container-owners-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6 mb-3">
            <?= $form->field($model, 'owner_name')->textInput(['maxlength' => true, 'placeholder' => 'e.g. Maersk Logistics']) ?>
        </div>
        
        <div class="col-md-6 mb-3">
            <?= $form->field($model, 'owner_email')->textInput(['maxlength' => true, 'placeholder' => 'contact@company.com']) ?>
        </div>

        <div class="col-md-6 mb-3">
            <?= $form->field($model, 'owner_contact')->textInput(['maxlength' => true, 'placeholder' => '+254 7...']) ?>
        </div>
    </div>

    <div class="form-group pt-3 border-top">
        <?= Html::submitButton('<i class="fa fa-save"></i> Save Client', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-alt-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>