<?php

use helpers\Html;
use helpers\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var dashboard\models\MasterContainerTypes $model */
/** @var helpers\widgets\ActiveForm $form */
?>

<div class="master-container-types-form">
    <?php $form = ActiveForm::begin(['options' => ['data-pjax' => true]]);?>
    <div class="row">
        <div class="col-md-12">
          <?= $form->field($model, 'iso_code')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'size')->textInput() ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'type_group')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <div class="block-content block-content-full text-center">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
