<?php

use helpers\Html;
use yii\widgets\Pjax;
use helpers\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var dashboard\models\ContainerVisits $model */
/** @var helpers\widgets\ActiveForm $form */
?>
<?php Pjax::begin() ?>
<div class="container-visits-form">
    <?php $form = ActiveForm::begin(['options' => ['data-pjax' => false]]);?>
    <div class="row">
        <div class="col-md-12">
          <?= $form->field($model, 'container_number')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'line_id')->textInput() ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'type_id')->textInput() ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'status')->dropDownList([ 'GATE_IN' => 'GATE IN', 'SURVEYED' => 'SURVEYED', 'IN_YARD' => 'IN YARD', 'RELEASE_ORDER' => 'RELEASE ORDER', 'GATE_OUT' => 'GATE OUT', ], ['prompt' => '']) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'current_condition')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'ticket_no_in')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'date_in')->textInput() ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'time_in')->textInput() ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'truck_no_in')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'driver_name_in')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'driver_id_in')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'ticket_no_out')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'date_out')->textInput() ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'time_out')->textInput() ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'truck_no_out')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'driver_name_out')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'destination')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'created_at')->textInput() ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'updated_at')->textInput() ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'created_by')->textInput() ?>
        </div>
    </div>
    <div class="block-content block-content-full text-center">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php Pjax::end() ?>