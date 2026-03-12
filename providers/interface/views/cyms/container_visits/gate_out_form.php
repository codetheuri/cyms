<?php

use yii\helpers\Html;
use yii\helpers\Url; // Needed for Url::to
use helpers\widgets\ActiveForm;
use dashboard\models\BillingRecords;

$this->title = 'Gate OUT Release';

// 1. FETCH CONTEXT DATA
$bill = BillingRecords::findOne(['visit_id' => $model->visit_id]);
$days = $bill ? $bill->storage_days : 0;
$status = $bill ? $bill->status : 'UNKNOWN';

if ($model->is_truck_only) {
    if ($model->date_in) {
        $start = strtotime($model->date_in . ' ' . ($model->time_in ?: '00:00:00'));
        $days = (time() - $start < 0) ? 1 : floor((time() - $start) / 86400) + 1;
    }
    $status = 'N/A';
}

// Lift On Check
$settingLiftOn = (float) Yii::$app->config->get('lift_on_charges');
$hasLiftOn = $bill && ($bill->lift_charges >= $settingLiftOn);

// Status Styles
$statusColor = match ($status) {
    'PAID' => 'success',
    'CREDIT' => 'info',
    'N/A' => 'secondary',
    default => 'danger'
};
$statusIcon = match ($status) {
    'PAID' => 'fa fa-check-circle',
    'CREDIT' => 'fa fa-file-signature',
    'N/A' => 'fa fa-ban',
    default => 'fa fa-times-circle'
};

// Check for Flag comments
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
    <div class="block-content block-content-full py-4">
        <div class="row align-items-center">

            <div class="col-md-4 border-end">
                <div class="fs-sm text-muted text-uppercase fw-bold mb-1">Releasing <?= $model->is_truck_only ? 'Truck' : 'Container' ?></div>
                <div class="fs-2 fw-bold text-dark d-flex align-items-center">
                    <i class="fa fa-truck-loading text-secondary me-2 opacity-50"></i>
                    <?= $model->is_truck_only ? '<span class="badge bg-secondary">Truck Only</span>' : Html::encode($model->container_number) ?>
                </div>
                <div class="d-flex align-items-center mt-1">
                    <?php if (!$model->is_truck_only): ?>
                        <span class="badge bg-secondary me-2"><?= $model->containerType->iso_code ?? 'Type N/A' ?></span>
                    <?php endif; ?>
                    <span class="fs-sm text-muted">Ticket: <strong><?= $model->ticket_no_in ?></strong></span>
                </div>
            </div>

            <div class="col-md-4 border-end text-center">
                <div class="fs-sm text-muted text-uppercase fw-bold mb-1">Stay Duration</div>
                <div class="d-flex justify-content-center align-items-center fs-sm fw-bold mb-1">
                    <span class="text-muted"><?= Yii::$app->formatter->asDate($model->date_in, 'php:d M') ?></span>
                    <i class="fa fa-long-arrow-alt-right mx-2 text-muted"></i>
                    <span class="text-primary">Now</span>
                </div>
                <div class="fs-4 fw-bold text-dark"><?= $days ?> <small class="fs-sm text-muted fw-normal">Days</small></div>

                <?php if (!$model->is_truck_only): ?>
                    <?php if ($hasLiftOn): ?>
                        <span class="badge bg-success-light text-success mt-1">
                            <i class="fa fa-crane me-1"></i> Lift On Paid
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning-light text-warning mt-1">
                            <i class="fa fa-user me-1"></i> Self Loading
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="col-md-4 text-center">
                <div class="fs-sm text-muted text-uppercase fw-bold mb-1">Clearance Status</div>
                <div class="fs-2 text-<?= $statusColor ?>">
                    <i class="<?= $statusIcon ?>"></i>
                </div>
                <div class="fw-bold text-<?= $statusColor ?> fs-5 mb-2">
                    <?= $model->is_truck_only ? 'NOT APPLICABLE' : 'CLEARED VIA ' . $status ?>
                </div>

                <?php if ($bill): ?>
                    <a href="<?= Url::to(['/dashboard/billing/view', 'id' => $bill->bill_id]) ?>" target="_blank" class="btn btn-sm btn-alt-secondary rounded-pill px-3">
                        <i class="fa fa-file-invoice-dollar me-1"></i> View Invoice
                    </a>
                <?php endif; ?>

                <?php if ($status === 'CREDIT'): ?>
                    <div class="fs-xs text-muted mt-2">Auth: <?= $bill->authorized_by ?></div>
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
                    <a href="<?= Url::to(['out-index']) ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-times me-1"></i> Cancel
                    </a>
                </div>
            </div>

            <div class="block-content block-content-full">
                <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

                <div class="row g-4">

                    <?php if (!$model->is_truck_only): ?>
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
                    <?php endif; ?>

                    <div class="col-md-<?= $model->is_truck_only ? '12' : '6' ?>">
                        <h6 class="text-uppercase text-muted fw-bold border-bottom pb-2 mb-3">
                            <i class="fa fa-map-marker-alt me-2"></i>Logistics & Timing
                        </h6>

                        <div class="alert alert-warning py-2 fs-sm">
                            <i class="fa fa-clock me-1"></i>
                            <strong>Backdating:</strong> If the truck left yesterday, please correct the date below.
                            The billing days will freeze based on the Date selected here.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <?= $form->field($model, 'date_out')->input('date', [
                                    'class' => 'form-control fw-bold',
                                    'max' => date('Y-m-d')
                                ])->label('Actual Date Out') ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <?= $form->field($model, 'time_out')->input('time', [
                                    'class' => 'form-control fw-bold'
                                ])->label('Actual Time Out') ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <?= $form->field($model, 'destination')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'e.g. Mombasa Port, Nairobi ICD...'
                            ])->label('Container Destination') ?>
                        </div>

                        <div class="mb-3">
                            <?= $form->field($model, 'comments_out')->textarea([
                                'class' => 'form-control',
                                'rows' => 3,
                                'placeholder' => 'Add notes about this exit (e.g. Truck leaving with empty container MSCU1234567)...'
                            ])->label('Gate Out Remarks / Notes') ?>
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
                        <div class="col-md-6">
                            <a href="<?= Url::to(['out-index']) ?>" class="btn btn-lg btn-alt-secondary px-4">
                                <i class="fa fa-times me-2"></i> Cancel
                            </a>
                        </div>
                        <div class="col-md-6 text-end">
                            <?= Html::submitButton('<i class="fa fa-truck-moving me-2"></i> RELEASE ' . ($model->is_truck_only ? 'TRUCK' : 'CONTAINER'), [
                                'class' => 'btn btn-lg btn-danger px-5 fw-bold shadow',
                                'data' => ['confirm' => 'Are you sure you want to gate out this unit? This action is final.']
                            ]) ?>
                        </div>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>