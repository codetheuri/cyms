<?php
use yii\helpers\Html;
use helpers\widgets\ActiveForm;
use dashboard\models\BillingRecords;

$this->title = 'Gate OUT Release';

// 1. FETCH CONTEXT DATA
$bill = BillingRecords::findOne(['visit_id' => $model->visit_id]);
$days = $bill ? $bill->storage_days : 0;
$status = $bill ? $bill->status : 'UNKNOWN';

// Lift On Check
$settingLiftOn = (float) Yii::$app->config->get('lift_on_charges');
$hasLiftOn = $bill && ($bill->lift_charges >= $settingLiftOn); // Simplified check

// Status Styles
$statusColor = match($status) {
    'PAID' => 'success',
    'CREDIT' => 'info',
    default => 'danger'
};
$statusIcon = match($status) {
    'PAID' => 'fa fa-check-circle',
    'CREDIT' => 'fa fa-file-signature',
    default => 'fa fa-times-circle'
};
$hasComments = !empty($model->comments_in);
?>
<?php if ($hasComments): ?>
    <div class="alert alert-danger border-3 border-danger shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <div class="fs-1 me-3"><i class="fa fa-hand-paper"></i></div>
            <div>
                <h4 class="alert-heading fw-bold mb-1">STOP! Read Instructions First</h4>
                <p class="mb-0 fs-5">
                    Note on Entry: <strong>"<?= Html::encode($model->comments_in) ?>"</strong>
                </p>
            </div>
        </div>
        <hr>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="confirm_override" required>
            <label class="form-check-label fw-bold" for="confirm_override">
                I have read the note and I am authorized to release this container despite the warning.
            </label>
        </div>
    </div>
<?php endif; ?>
<div class="block block-rounded border-start border-5 border-<?= $statusColor ?> shadow-sm mb-4">
    <div class="block-content block-content-full py-3">
        <div class="row align-items-center">
            
            <div class="col-md-4 border-end">
                <div class="fs-sm text-muted text-uppercase fw-bold mb-1">Releasing Container</div>
                <div class="fs-3 fw-bold text-dark">
                    <i class="fa fa-truck-loading me-2 text-secondary"></i><?= $model->container_number ?>
                </div>
                <div class="badge bg-secondary mt-1">
                    <?= $model->containerType->iso_code ?? 'Type N/A' ?>
                </div>
            </div>

            <div class="col-md-4 border-end text-center">
                <div class="fs-sm text-muted text-uppercase fw-bold mb-1">Stay Duration</div>
                <div class="d-flex justify-content-center align-items-center fs-sm fw-bold">
                    <span class="text-muted"><?= Yii::$app->formatter->asDate($model->date_in, 'php:d M') ?></span>
                    <i class="fa fa-long-arrow-alt-right mx-2 text-muted"></i>
                    <span class="text-primary">Now</span>
                </div>
                <div class="fs-4 fw-bold text-dark"><?= $days ?> <small class="fs-sm text-muted fw-normal">Days</small></div>
                
                <?php if ($hasLiftOn): ?>
                    <span class="badge bg-success-light text-success mt-1">
                        <i class="fa fa-crane me-1"></i> Lift On Paid
                    </span>
                <?php else: ?>
                    <span class="badge bg-warning-light text-warning mt-1">
                        <i class="fa fa-user me-1"></i> Self Loading
                    </span>
                <?php endif; ?>
            </div>

            <div class="col-md-4 text-center">
                <div class="fs-sm text-muted text-uppercase fw-bold mb-1">Clearance Status</div>
                <div class="fs-2 text-<?= $statusColor ?>">
                    <i class="<?= $statusIcon ?>"></i>
                </div>
                <div class="fw-bold text-<?= $statusColor ?> fs-5">
                    CLEARED VIA <?= $status ?>
                </div>
                <?php if ($status === 'CREDIT'): ?>
                    <small class="text-muted">Auth: <?= $bill->authorized_by ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="block block-rounded content-card">
            <div class="block-header block-header-default bg-body-light">
                <h3 class="block-title fw-bold text-dark">
                    <i class="fa fa-pen-alt me-2 text-muted"></i> Gate Out Details
                </h3>
                <div class="block-options">
                    <?= Html::a('Cancel', ['out-index'], ['class' => 'btn btn-sm btn-alt-secondary']) ?>
                </div>
            </div>
            
            <div class="block-content block-content-full">
                <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

                <div class="row g-4">
                    
                    <div class="col-md-6 border-end">
                        <h6 class="text-uppercase text-muted fw-bold border-bottom pb-2 mb-3">
                            <i class="fa fa-truck me-2"></i>Picking Vehicle
                        </h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <?= $form->field($model, 'vehicle_reg_no_out')->textInput([
                                    'class' => 'form-control form-control-lg',
                                    'placeholder' => 'KAA 123A'
                                ])->label('Truck Reg No.') ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <?= $form->field($model, 'trailer_reg_no_out')->textInput([
                                    'class' => 'form-control form-control-lg',
                                    'placeholder' => 'ZA 456'
                                ])->label('Trailer No.') ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <?= $form->field($model, 'truck_type_out')->dropDownList(
                                ['TR' => 'Trailer', 'TAST' => 'Canter/Lorry', 'SIDELOADER' => 'Side Loader', 'OTHER' => 'Other'], 
                                ['class' => 'form-select']
                            ) ?>
                        </div>

                        <h6 class="text-uppercase text-muted fw-bold border-bottom pb-2 mb-3 mt-4">
                            <i class="fa fa-id-card me-2"></i>Driver Details
                        </h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <?= $form->field($model, 'driver_name_out')->textInput(['placeholder' => 'Full Name']) ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <?= $form->field($model, 'driver_id_out')->textInput(['placeholder' => 'ID / License']) ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted fw-bold border-bottom pb-2 mb-3">
                            <i class="fa fa-map-marker-alt me-2"></i>Logistics
                        </h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <?= $form->field($model, 'date_out')->input('date', ['readonly' => true]) ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <?= $form->field($model, 'time_out')->input('time', ['readonly' => true]) ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <?= $form->field($model, 'destination')->textInput([
                                'class' => 'form-control form-control-lg', 
                                'placeholder' => 'e.g. Mombasa Port, Nairobi ICD...'
                            ])->label('Container Destination') ?>
                        </div>

                        <div class="alert alert-warning py-2">
                             <small><i class="fa fa-exclamation-circle"></i> Ensure the Ticket No matches the printed Gate Pass.</small>
                        </div>
                        
                        <div class="mb-3">
                             <?= $form->field($model, 'ticket_no_out')->textInput(['readonly' => true, 'class' => 'form-control bg-body-light fw-bold']) ?>
                        </div>

                        <h6 class="text-uppercase text-muted fw-bold border-bottom pb-2 mb-3 mt-4">
                            <i class="fa fa-camera me-2"></i>Evidence
                        </h6>
                        
                        <div class="p-3 bg-body-light rounded border border-dashed text-center">
                            <?= $form->field($model, 'departure_photo_file')->fileInput(['accept' => 'image/*', 'class' => 'form-control'])->label(false) ?>
                            <small class="text-muted">Capture photo of container on truck leaving.</small>
                        </div>
                    </div>
                </div>

                <div class="pt-4 mt-4 border-top">
                    <div class="row align-items-center">
                        <div class="col-md-8 text-muted fs-sm">
                            By clicking release, you confirm that the container has physically left the yard and all fees have been settled.
                        </div>
                        <div class="col-md-4 text-end">
                            <?= Html::submitButton('<i class="fa fa-truck-moving me-2"></i> RELEASE CONTAINER', [
                                'class' => 'btn btn-lg btn-danger w-100 fw-bold shadow',
                                'data' => ['confirm' => 'Are you sure you want to gate out this container? This action is final.']
                            ]) ?>
                        </div>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>