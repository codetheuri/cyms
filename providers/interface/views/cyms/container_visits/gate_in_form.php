<?php

use yii\helpers\Url;
use yii\helpers\Html;
use helpers\widgets\ActiveForm;

/* @var yii\web\View $this */
/* @var dashboard\models\ContainerVisits $model */
/* @var array $shippingLines */
/* @var array $owners */
/* @var array $types */

$isNew = $model->isNewRecord;
$this->title = $isNew ? 'New Gate IN Entry' : 'Update Gate IN Entry';
$readOnlyAttr = ['readonly' => !$isNew];

// Get current date/time for the HTML5 max attributes to prevent future selection visually
$today = date('Y-m-d');
$nowTime = date('H:i');

// --- 1. LOAD SELECT2 FROM CDN ---
$this->registerCssFile("https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css");
$this->registerJsFile("https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js", ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<div class="block block-rounded shadow-sm">
    <div class="block-header block-header-default bg-body-light border-bottom">
        <h3 class="block-title fw-bold">
            <i class="fa <?= $isNew ? 'fa-truck-ramp-box text-success' : 'fa-pen text-primary' ?> me-2"></i> 
            <?= $this->title ?>
        </h3>
        <div class="block-options">
            <?= Html::a('<i class="fa fa-arrow-left me-1"></i> Back to List', ['index'], ['class' => 'btn btn-sm btn-alt-secondary fw-bold']) ?>
        </div>
    </div>
    
    <div class="block-content block-content-full p-4">

        <?php if ($model->hasErrors()): ?>
            <div class="alert alert-danger d-flex align-items-center mb-4">
                <i class="fa fa-exclamation-circle fa-2x me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1 fw-bold">Please fix the following errors:</h5>
                    <?= Html::errorSummary($model, ['class' => 'mb-0 fs-sm ps-3']) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data'],
            'errorCssClass' => 'is-invalid',
            'successCssClass' => 'is-valid',
        ]); ?>

        <div class="p-3 bg-body-extra-light rounded border mb-4">
            <h5 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="fa fa-clock me-2"></i> Arrival Timing & Container</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <?= $form->field($model, 'ticket_no_in')->textInput([
                        'readonly' => true, 
                        'class' => 'form-control font-monospace bg-light',
                        'placeholder' => 'Auto-Generated'
                    ]) ?>
                </div>
                
                <div class="col-md-3">
                    <?= $form->field($model, 'date_in')->textInput([
                        'type' => 'date', 
                        'max' => $today, // Prevents selecting future dates
                        'class' => 'form-control fw-bold text-dark'
                    ])->label('Date IN <span class="text-danger">*</span>') ?>
                </div>
                
                <div class="col-md-2">
                    <?= $form->field($model, 'time_in')->textInput([
                        'type' => 'time', 
                        'class' => 'form-control fw-bold text-dark'
                    ])->label('Time IN <span class="text-danger">*</span>') ?>
                </div>

                <div class="col-md-4">
                    <?= $form->field($model, 'container_number')->textInput(array_merge(
                        [
                            'class' => 'form-control text-uppercase fw-bold fs-lg text-primary border-primary',
                            'placeholder' => 'MSCU1234567',
                            'maxlength' => 11,
                            'oninput' => "this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')"
                        ],
                        $readOnlyAttr
                    ))->label('Container Number <span class="text-danger">*</span>') ?>
                </div>
            </div>
        </div>

        <div class="p-3 bg-body-extra-light rounded border mb-4">
            <h5 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="fa fa-boxes me-2"></i> Container Specs & Shipping</h5>
            <div class="row g-3">
                
                <div class="col-md-4">
                    <label class="form-label fw-bold">Type / Size <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <?= $form->field($model, 'container_type_id', ['options' => ['tag' => false]])->dropDownList(
                            $types,
                            ['prompt' => 'Select Type...', 'class' => 'form-select select2-hidden-accessible', 'id' => 'type-dropdown']
                        )->label(false) ?>
                        <button type="button" class="btn btn-alt-primary" data-bs-toggle="modal" data-bs-target="#addTypeModal" title="Add New Type">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Shipping Line <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <?= $form->field($model, 'shipping_line_id', ['options' => ['tag' => false]])->dropDownList(
                            $shippingLines,
                            ['prompt' => 'Select Line...', 'class' => 'form-select select2-hidden-accessible', 'id' => 'line-dropdown']
                        )->label(false) ?>
                        <button type="button" class="btn btn-alt-primary" data-bs-toggle="modal" data-bs-target="#addLineModal" title="Add New Line">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <?= $form->field($model, 'seal_number_in')->textInput(['placeholder' => 'e.g. 123456', 'class' => 'form-control text-uppercase']) ?>
                </div>

                <div class="col-md-4">
                    <?= $form->field($model, 'shipping_agent_name')->textInput(['placeholder' => 'Agent Name']) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'vessel_name')->textInput(['placeholder' => 'Vessel Name']) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'voyage_number')->textInput(['placeholder' => 'Voyage No']) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'bl_number')->textInput(['placeholder' => 'BL No']) ?>
                </div>
            </div>
        </div>

        <div class="p-3 bg-body-extra-light rounded border mb-4">
            <h5 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="fa fa-truck me-2"></i> Transport Logistics</h5>
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label fw-bold">Container Owner / Transporter <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <?= $form->field($model, 'container_owner_id', ['options' => ['tag' => false]])->dropDownList(
                            $owners,
                            ['prompt' => 'Search Owner...', 'class' => 'form-select select2-hidden-accessible', 'id' => 'owner-dropdown']
                        )->label(false) ?>
                        <button type="button" class="btn btn-alt-primary" data-bs-toggle="modal" data-bs-target="#addOwnerModal" title="Add New Owner">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model, 'party_delivering_container')->textInput([
                        'placeholder' => 'Company delivering unit (e.g. REGAL FREIGHT LTD)',
                        'class' => 'form-control text-uppercase'
                    ]) ?>
                </div>
                
                <div class="col-md-3">
                    <?= $form->field($model, 'vehicle_reg_no_in')->textInput([
                        'class' => 'form-control text-uppercase fw-bold', 
                        'placeholder' => 'KCA 123A'
                    ])->label('Truck Reg No. <span class="text-danger">*</span>') ?>
                </div>
                
                <div class="col-md-3">
                    <?= $form->field($model, 'trailer_reg_no_in')->textInput([
                        'class' => 'form-control text-uppercase', 
                        'placeholder' => 'ZC 4567'
                    ])->label('Trailer Reg No.') ?>
                </div>
                
                <div class="col-md-2">
                    <?= $form->field($model, 'truck_type_in')->dropDownList(
                        ['TR' => 'Tractor (TR)', 'TAST' => 'Trailer (TAST)'], 
                        ['prompt' => 'Select...']
                    )->label('Truck Type') ?>
                </div>

                <div class="col-md-4">
                    <?= $form->field($model, 'driver_name_in')->textInput(['placeholder' => 'Full Name'])->label('Driver Name <span class="text-danger">*</span>') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'driver_id_in')->textInput(['placeholder' => 'National ID / Passport']) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'truck_owner_contact_in')->textInput(['placeholder' => 'Phone Number']) ?>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-7">
                <div class="p-3 bg-body-extra-light rounded border h-100">
                    <h5 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="fa fa-camera me-2"></i> Documentation</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Arrival Photo</label>
                            <?= $form->field($model, 'arrival_photo_file')->fileInput(['class' => 'form-control', 'accept' => 'image/*'])->label(false) ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Manifest / EIR Documents</label>
                            <?= $form->field($model, 'document_files[]')->fileInput(['class' => 'form-control', 'multiple' => true, 'accept' => 'image/*,application/pdf'])->label(false) ?>
                            <div class="form-text fs-xs text-muted">Hold CTRL to select multiple files.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="p-3 bg-warning-light rounded border border-warning border-2 h-100 shadow-sm">
                    <h5 class="text-warning-dark fw-bold border-bottom border-warning pb-2 mb-3">
                        <i class="fa fa-exclamation-triangle me-2"></i> Warning Flags
                    </h5>
                    <?= $form->field($model, 'comments_in')->textarea([
                        'rows' => 3,
                        'class' => 'form-control border-warning',
                        'placeholder' => 'E.g., "Police Case - DO NOT RELEASE", "Damaged heavily on arrival"...'
                    ])->label(false) ?>
                    <div class="form-text text-dark fs-sm mt-2">
                        <i class="fa fa-info-circle text-warning me-1"></i> Notes here will alert users during Gate Out.
                    </div>
                </div>
            </div>
        </div>

        <div class="p-3 bg-body-light rounded border text-end mt-4 shadow-sm">
            <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-alt-secondary me-2 px-4 fw-bold']) ?>
            <?= Html::submitButton('<i class="fa fa-check-circle me-1"></i> Save Gate IN Record', ['class' => 'btn btn-primary px-5 fw-bold']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="modal fade" id="addOwnerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-body-light">
                <h5 class="modal-title fw-bold">Add Owner</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-owner">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="MasterContainerOwners[owner_name]" class="form-control form-control-lg" required></div>
                    <div class="mb-4"><label class="form-label">Contact</label><input type="text" name="MasterContainerOwners[owner_contact]" class="form-control form-control-lg"></div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Save Owner</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addLineModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-body-light">
                <h5 class="modal-title fw-bold">Add Shipping Line</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-line">
                    <div class="mb-3"><label class="form-label">Code</label><input type="text" name="MasterShippingLines[line_code]" class="form-control form-control-lg text-uppercase" placeholder="e.g. MSC" required></div>
                    <div class="mb-4"><label class="form-label">Name</label><input type="text" name="MasterShippingLines[line_name]" class="form-control form-control-lg" required></div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Save Line</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addTypeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-body-light">
                <h5 class="modal-title fw-bold">Add Container Type</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-type">
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">ISO Code</label><input type="text" name="MasterContainerTypes[iso_code]" class="form-control form-control-lg text-uppercase" placeholder="45G1" required></div>
                        <div class="col-6 mb-3"><label class="form-label">Size (ft)</label><input type="number" name="MasterContainerTypes[size]" class="form-control form-control-lg" placeholder="40" required></div>
                    </div>
                    <div class="mb-4"><label class="form-label">Group</label><input type="text" name="MasterContainerTypes[type_group]" class="form-control form-control-lg text-uppercase" placeholder="HC, GP, RE" required></div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Save Type</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$urlOwner = Url::to(['/dashboard/visit/ajax-create-owner']);
$urlLine  = Url::to(['/dashboard/shipping-line/ajax-create']);
$urlType  = Url::to(['/dashboard/container-type/ajax-create']);

$script = <<< JS
    // 1. INITIALIZE SELECT2 FOR SEARCHABLE DROPDOWNS
    $(document).ready(function() {
        $('#owner-dropdown, #line-dropdown, #type-dropdown').select2({
            width: '100%'
        });
    });

    // 2. QUICK ADD LOGIC
    function setupQuickAdd(formId, url, dropdownId, modalId) {
        $(formId).on('submit', function(e) {
            e.preventDefault();
            var submitBtn = $(this).find('button[type="submit"]');
            var originalText = submitBtn.text();
            submitBtn.text('Saving...').prop('disabled', true);

            $.ajax({
                url: url, type: 'POST', data: $(this).serialize(),
                success: function(res) {
                    if(res.success) {
                        var newOption = new Option(res.name, res.id, true, true);
                        $(dropdownId).append(newOption).trigger('change');
                        bootstrap.Modal.getInstance(document.querySelector(modalId)).hide();
                        $(formId)[0].reset();
                    } else { alert('Error saving data.'); }
                },
                complete: function() {
                    submitBtn.text(originalText).prop('disabled', false);
                }
            });
        });
    }

    setupQuickAdd('#form-owner', '$urlOwner', '#owner-dropdown', '#addOwnerModal');
    setupQuickAdd('#form-line',  '$urlLine',  '#line-dropdown',  '#addLineModal');
    setupQuickAdd('#form-type',  '$urlType',  '#type-dropdown',  '#addTypeModal');
JS;
$this->registerJs($script);
?>