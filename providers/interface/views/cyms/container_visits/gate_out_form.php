<?php
use yii\helpers\Html;
use helpers\widgets\ActiveForm;

$this->title = 'Gate OUT Release';
?>

<div class="block block-rounded content-card border-start border-3 border-danger">
    <div class="block-header block-header-default">
        <h3 class="block-title text-danger fw-bold">Confirm Release: <?= $model->container_number ?></h3>
        <div class="block-options">
            <?= Html::a('Cancel', ['out-index'], ['class' => 'btn btn-sm btn-alt-secondary']) ?>
        </div>
    </div>
    <div class="block-content block-content-full">
        
        <div class="alert alert-info mb-4">
            <i class="fa fa-info-circle me-1"></i> 
            Ticket IN: <strong><?= $model->ticket_no_in ?></strong> | 
            Date IN: <strong><?= $model->date_in ?></strong>
        </div>

        <?php $form = ActiveForm::begin(); ?>

        <!-- TICKET OUT -->
        <h5 class="border-bottom pb-2 mb-3 text-danger">Exit Details</h5>
        <div class="row">
            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'ticket_no_out')->textInput(['readonly' => true, 'placeholder' => 'Auto-Generated']) ?>
            </div>
            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'date_out')->input('date') ?>
            </div>
            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'time_out')->input('time') ?>
            </div>
        </div>

        <!-- VEHICLE OUT -->
        <h5 class="border-bottom pb-2 mb-3 mt-3 text-danger">Picking Vehicle</h5>
        <div class="row">
            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'vehicle_reg_no_out')->textInput() ?>
            </div>
            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'truck_type_out')->dropDownList(['TR' => 'TR', 'TAST' => 'TAST', 'OTHER' => 'Other'], ['prompt' => 'Select Type...']) ?>
            </div>
            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'trailer_reg_no_out')->textInput() ?>
            </div>
        </div>

        <!-- DRIVER OUT & DESTINATION -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <?= $form->field($model, 'driver_name_out')->textInput() ?>
            </div>
            <div class="col-md-6 mb-3">
                <?= $form->field($model, 'driver_id_out')->textInput() ?>
            </div>
            <div class="col-md-12 mb-3">
                <?= $form->field($model, 'destination')->textInput(['placeholder' => 'e.g. Nairobi, Kampala...']) ?>
            </div>
            <h5 class="border-bottom pb-2 mb-3 mt-3 text-danger">Departure Evidence</h5>
<div class="row">
    <div class="col-md-12">
        <?= $form->field($model, 'departure_photo_file')->fileInput(['accept' => 'image/*'])->label('Gate Out Photo') ?>
    </div>
</div>
        </div>

        <div class="pt-3 border-top mt-3">
            <?= Html::submitButton('<i class="fa fa-truck-moving me-1"></i> Release Container', ['class' => 'btn btn-lg btn-danger']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>