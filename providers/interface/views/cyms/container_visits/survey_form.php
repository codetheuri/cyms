<?php
use yii\helpers\Html;
use helpers\widgets\ActiveForm;
use dashboard\models\MasterRepairCodes;

/* @var $this yii\web\View */
/* @var $visit dashboard\models\ContainerVisits */
/* @var $survey dashboard\models\ContainerSurveys */
/* @var $damages dashboard\models\SurveyDamages[] */

$this->title = 'Perform Container Survey';
$this->params['breadcrumbs'][] = ['label' => 'Gate IN', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Survey';

$repairCodes = MasterRepairCodes::getDropdownList();
$repairDataJson = json_encode(MasterRepairCodes::getJsData());

// Helpers for display
$type = $visit->containerType ? $visit->containerType->size . "' " . $visit->containerType->type_group : 'Unknown';
$line = $visit->shippingLine ? $visit->shippingLine->line_name : 'Unknown';
$owner = $visit->containerOwner ? $visit->containerOwner->owner_name : ($visit->truck_owner_name_in ?? 'N/A');
?>

<div class="row justify-content-center">
    <div class="col-xl-12">
        
        <?php $form = ActiveForm::begin(['id' => 'dynamic-form']); ?>

        <div class="block block-rounded content-card mb-3">
            <div class="block-content p-4 bg-body-light">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h3 class="block-title fs-3 fw-bold text-primary mb-1">
                            <?= $visit->container_number ?>
                        </h3>
                        <span class="badge bg-primary fs-sm"><?= $type ?></span>
                        <span class="badge bg-secondary fs-sm"><?= $line ?></span>
                    </div>
                    <div class="text-end">
                        <div class="fs-sm text-muted text-uppercase">Ticket No</div>
                        <div class="fw-bold text-dark"><?= $visit->ticket_no_in ?></div>
                    </div>
                </div>

                <div class="row g-4 text-center border-top pt-3">
                    <div class="col-md-3 col-6 border-end">
                        <div class="fs-sm text-muted text-uppercase"><i class="fa fa-calendar-alt me-1"></i> Date IN</div>
                        <div class="fw-bold"><?= Yii::$app->formatter->asDate($visit->date_in) ?></div>
                    </div>
                    <div class="col-md-3 col-6 border-end">
                        <div class="fs-sm text-muted text-uppercase"><i class="fa fa-user-tie me-1"></i> Client</div>
                        <div class="fw-bold text-truncate"><?= $owner ?></div>
                    </div>
                    <div class="col-md-3 col-6 border-end">
                        <div class="fs-sm text-muted text-uppercase"><i class="fa fa-truck me-1"></i> Truck</div>
                        <div class="fw-bold"><?= $visit->vehicle_reg_no_in ?></div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="fs-sm text-muted text-uppercase"><i class="fa fa-user me-1"></i> Driver</div>
                        <div class="fw-bold"><?= $visit->driver_name_in ?></div>
                    </div>
                </div>
            </div>
            
            <div class="block-content block-content-full border-top">
                <div class="row g-3">
                    <div class="col-md-12 mb-1">
                        <label class="fs-xs fw-bold text-uppercase text-primary">CSC Plate Details (Verification)</label>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($visit, 'gross_weight')->textInput(['type' => 'number', 'class' => 'form-control form-control-sm', 'placeholder' => 'Max Gross']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($visit, 'tare_weight')->textInput(['type' => 'number', 'class' => 'form-control form-control-sm', 'placeholder' => 'Tare']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($visit, 'payload')->textInput(['type' => 'number', 'class' => 'form-control form-control-sm', 'placeholder' => 'Payload']) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-sm">Assign Yard Slot</label>
                        <?= Html::dropDownList('assign_slot_id', $currentSlotId ?? null, $slotList ?? [], [
                            'class' => 'form-select form-select-sm',
                            'prompt' => 'Unassigned...'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

       

            <div class="block-content">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <?= $form->field($survey, 'surveyor_name')->textInput(['class' => 'form-control', 'readonly' => true, 'value' => Yii::$app->user->identity->username ?? 'Admin']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($survey, 'survey_date')->textInput(['type' => 'datetime-local', 'class' => 'form-control', 'value' => date('Y-m-d\TH:i')]) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Evidence Photo</label>
                        <?= $form->field($survey, 'survey_photo_file')->fileInput(['accept' => 'image/*', 'class' => 'form-control'])->label(false) ?>
                        <?php if ($survey->survey_photo_path): ?>
                            <div class="mt-1">
                                <a href="<?= Yii::getAlias('@web') . '/' . $survey->survey_photo_path ?>" target="_blank" class="fs-xs">View Current Photo</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-vcenter" id="damages-table">
                        <thead class="bg-gray-light">
                            <tr class="text-uppercase fs-xs">
                                <th style="width: 18%;">Repair Code</th>
                                <th style="width: 25%;">Description</th>
                                <th style="width: 8%;">Qty</th>
                                <th style="width: 8%;">Hrs</th>
                                <th style="width: 12%;">Labor</th>
                                <th style="width: 12%;">Material</th>
                                <th style="width: 12%;">Total</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($damages as $i => $damage): ?>
                                <tr class="damage-row">
                                    <?php if (!$damage->isNewRecord) echo Html::activeHiddenInput($damage, "[$i]damage_id"); ?>

                                    <td>
                                        <?= $form->field($damage, "[$i]repair_code")->dropDownList($repairCodes, ['class' => 'form-select form-select-sm code-select', 'prompt' => 'Select...'])->label(false) ?>
                                    </td>
                                    <td>
                                        <?= $form->field($damage, "[$i]description")->textInput(['class' => 'form-control form-control-sm desc-input', 'readonly' => true, 'placeholder' => 'Auto-fill'])->label(false) ?>
                                    </td>
                                    <td>
                                        <?= $form->field($damage, "[$i]quantity")->textInput(['class' => 'form-control form-control-sm qty-input text-center', 'type' => 'number', 'min' => 1])->label(false) ?>
                                    </td>
                                    <td>
                                        <?= $form->field($damage, "[$i]hours")->textInput(['class' => 'form-control form-control-sm hours-input calc-trigger text-center', 'type' => 'number', 'step' => '0.01'])->label(false) ?>
                                    </td>
                                    <td>
                                        <?= $form->field($damage, "[$i]labor_cost")->textInput(['class' => 'form-control form-control-sm labor-input calc-trigger text-end', 'readonly' => true])->label(false) ?>
                                    </td>
                                    <td>
                                        <?= $form->field($damage, "[$i]material_cost")->textInput(['class' => 'form-control form-control-sm material-input calc-trigger text-end', 'readonly' => false])->label(false) ?>
                                    </td>
                                    <td>
                                        <?= $form->field($damage, "[$i]total_cost")->textInput(['class' => 'form-control form-control-sm total-input fw-bold text-end', 'readonly' => true])->label(false) ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger remove-row-btn" title="Remove"><i class="fa fa-times"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div id="sound-container-msg" class="alert alert-success d-flex align-items-center justify-content-center py-4 mb-3" style="display: none;">
                    <div class="text-center">
                        <i class="fa fa-check-circle fa-2x mb-2"></i>
                        <h4 class="alert-heading fs-5 fw-bold mb-0">Container is Sound</h4>
                        <p class="mb-0 fs-sm">No damages recorded. Click "Add Damage" if needed.</p>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <button type="button" class="btn btn-sm btn-alt-primary" id="add-damage-btn">
                        <i class="fa fa-plus me-1"></i> Add Damage Row
                    </button>
                    <h4 class="mb-0 text-end">
                        Estimate: <span class="fs-sm text-muted">KES</span> 
                        <span id="grand-total-display" class="text-success fw-bold">0.00</span>
                    </h4>
                </div>

                <div class="bg-warning-light p-3 rounded border border-warning mb-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="text-warning fw-bold mb-0 fs-6"><i class="fa fa-file-invoice-dollar me-1"></i> Billing Authorization</h5>
                            <span class="fs-xs text-muted">Enable only if client approved repairs.</span>
                        </div>
                        <div class="form-check form-switch">
                            <?= Html::hiddenInput('ContainerSurveys[bill_repairs]', 0) ?>
                            <?= $form->field($survey, 'bill_repairs')->checkbox(['class' => 'form-check-input', 'label' => false]) ?>
                            <label class="form-check-label fw-bold">Bill Repairs</label>
                        </div>
                    </div>
                </div>

                <div class="row border-top pt-4">
                    <div class="col-6">
                        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-alt-secondary px-4']) ?>
                    </div>
                    <div class="col-6 text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa fa-save me-1"></i> Complete Survey
                        </button>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<?php
$script = <<< JS
$(document).ready(function() {
    var repairData = $repairDataJson;
    var rowCount = $(".damage-row").length;

    // Function to check if table is empty
    function checkEmptyState() {
        if ($("#damages-table tbody tr").length === 0) {
            $("#damages-table").hide();
            $("#sound-container-msg").show(); // Show "Sound" message
        } else {
            $("#damages-table").show();
            $("#sound-container-msg").hide();
        }
    }

    function updateRowTotal(row) {
        var hours = parseFloat(row.find(".hours-input").val()) || 0;
        var qty = parseFloat(row.find(".qty-input").val()) || 1;
        var labor = parseFloat(row.find(".labor-input").val()) || 0;
        var material = parseFloat(row.find(".material-input").val()) || 0;
        
                var totalmaterrial = (material) * qty;
        var total = ((hours * labor) + totalmaterrial);
        
        row.find(".total-input").val(total.toFixed(2));
        updateGrandTotal();
    }

    function updateGrandTotal() {
        var grandTotal = 0;
        $(".total-input").each(function() {
            grandTotal += parseFloat($(this).val()) || 0;
        });
        $("#grand-total-display").text(grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2}));
    }

    // 1. Auto-Fill on Dropdown Change
    $(document).on('change', '.code-select', function() {
        var code = $(this).val();
        var row = $(this).closest('tr');

        if (repairData[code]) {
            var data = repairData[code];
            row.find('.desc-input').val(data.description);
            row.find('.hours-input').val(data.hours);
            row.find('.material-input').val(data.material);
            row.find('.labor-input').val(data.labor);
            if(row.find('.qty-input').val() == "") { row.find('.qty-input').val(1); }
            updateRowTotal(row);
        }
    });

    // 2. Recalculate on Input
    $(document).on('input', '.calc-trigger, .qty-input', function() {
        updateRowTotal($(this).closest('tr'));
    });

    // 3. Add Row
    $("#add-damage-btn").click(function() {
        var tableBody = $("#damages-table tbody");
        
        // If table was empty/hidden, we need a template. 
        // Since we might have deleted all rows, we can't clone. 
        // We must reload page or keep a hidden template. 
        // A simpler hack for this prototype: Reload if 0 rows, or prevent deleting last row?
        // Better: Check if hidden. If hidden, show table and we need a mechanism to create a row.
        // For simplicity: We allowed deleting all rows. Now we must reconstruct.
        // Ideally, PHP renders one row always.
        
        if (tableBody.find("tr").length === 0) {
            // If user deleted everything, reload page to get a fresh row is the easiest "Quick Fix"
            // Or prevent deletion of last row.
            // Let's stick to: Prevent deletion of last row for simplicity OR
            // Just un-hide if it was hidden (if we didn't remove from DOM).
            // Actually, let's just Clone the last one BEFORE removing it? No.
            
            // SOLUTION: Just reload to start fresh is acceptable for "Sound" -> "Damaged" switch.
            location.reload(); 
            return;
        }

        var template = tableBody.find("tr:first").clone();
        template.find("input, select").each(function() {
            this.name = this.name.replace(/\[\d+\]/, "[" + rowCount + "]");
            this.id = this.id.replace(/-\d+-/, "-" + rowCount + "-");
            this.value = ""; 
            if($(this).hasClass('qty-input')) this.value = 1;
        });
        template.find("input[type='hidden']").remove();
        tableBody.append(template);
        rowCount++;
        checkEmptyState();
    });

    // 4. Remove Row (Updated to allow 0 rows)
    $(document).on("click", ".remove-row-btn", function() {
        $(this).closest("tr").remove();
        updateGrandTotal();
        checkEmptyState();
    });
    
    // Initialize
    updateGrandTotal();
    checkEmptyState();
});
JS;
$this->registerJs($script);
?>