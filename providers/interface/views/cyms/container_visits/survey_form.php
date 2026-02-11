<?php
use yii\helpers\Html;
use yii\helpers\Url;
use helpers\widgets\ActiveForm;
use dashboard\models\MasterRepairCodes;

/* @var $this yii\web\View */
/* @var $visit dashboard\models\ContainerVisits */
/* @var $survey dashboard\models\ContainerSurveys */
/* @var $damages dashboard\models\SurveyDamages[] */

$this->title = 'Perform Survey';
$this->params['breadcrumbs'][] = ['label' => 'Gate IN', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Survey';

$repairCodes = MasterRepairCodes::getDropdownList();
$repairDataJson = json_encode(MasterRepairCodes::getJsData());

// Helpers for display
$type = $visit->containerType ? $visit->containerType->size . "' " . $visit->containerType->type_group : 'Unknown';
$line = $visit->shippingLine ? $visit->shippingLine->line_name : 'Unknown';
$owner = $visit->containerOwner ? $visit->containerOwner->owner_name : ($visit->truck_owner_name_in ?? 'N/A');
$party = $visit->party_delivering_container ?? 'Not Specified'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
    <div>
        <h2 class="content-heading m-0 border-0 fw-bold text-dark">
            <i class="fa fa-clipboard-check me-2 text-primary"></i> Survey Form
        </h2>
        <div class="fs-sm text-muted">Ticket #<?= $visit->ticket_no_in ?></div>
    </div>
    <div>
        <?= Html::a('<i class="fa fa-times me-1"></i> Cancel / Back', ['index'], [
            'class' => 'btn btn-alt-secondary px-3 fw-bold'
        ]) ?>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-xl-12">
        
        <?php $form = ActiveForm::begin(['id' => 'dynamic-form']); ?>

        <div class="block block-rounded content-card mb-4 shadow-sm border-start border-4 border-primary">
            <div class="block-content block-content-full bg-body-light">
                <div class="row g-4">
                    <div class="col-md-3 border-end">
                        <div class="fs-xs fw-bold text-muted text-uppercase mb-1">Container</div>
                        <div class="fs-3 fw-bold text-primary"><?= $visit->container_number ?></div>
                        <div>
                            <span class="badge bg-secondary"><?= $type ?></span>
                            <span class="badge bg-info"><?= $line ?></span>
                        </div>
                    </div>

                    <div class="col-md-5 border-end">
                        <div class="row">
                            <div class="col-6 mb-2">
                                <div class="fs-xs fw-bold text-muted text-uppercase">Date In</div>
                                <div class="fw-bold text-dark"><?= Yii::$app->formatter->asDate($visit->date_in, 'php:d M Y') ?> <span class="text-muted fw-normal"><?= $visit->time_in ?></span></div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="fs-xs fw-bold text-muted text-uppercase">Party Delivering</div>
                                <div class="fw-bold text-dark"><?= $party ?></div>
                            </div>
                            <div class="col-6">
                                <div class="fs-xs fw-bold text-muted text-uppercase">Transporter</div>
                                <div class="fw-bold text-truncate"><?= $owner ?></div>
                            </div>
                            <div class="col-6">
                                <div class="fs-xs fw-bold text-muted text-uppercase">Truck / Driver</div>
                                <div><?= $visit->vehicle_reg_no_in ?> <small class="text-muted">(<?= $visit->driver_name_in ?>)</small></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="fs-xs fw-bold text-muted text-uppercase mb-2"><i class="fa fa-weight-hanging me-1"></i> CSC / Weights</div>
                        <div class="row g-2">
                            <div class="col-4">
                                <?= $form->field($visit, 'gross_weight')->textInput(['type' => 'number', 'class' => 'form-control form-control-sm text-center', 'placeholder' => 'Max'])->label(false) ?>
                                <div class="fs-xs text-center text-muted mt-1">Max</div>
                            </div>
                            <div class="col-4">
                                <?= $form->field($visit, 'tare_weight')->textInput(['type' => 'number', 'class' => 'form-control form-control-sm text-center', 'placeholder' => 'Tare'])->label(false) ?>
                                <div class="fs-xs text-center text-muted mt-1">Tare</div>
                            </div>
                            <div class="col-4">
                                <?= $form->field($visit, 'payload')->textInput(['type' => 'number', 'class' => 'form-control form-control-sm text-center', 'placeholder' => 'Pay'])->label(false) ?>
                                <div class="fs-xs text-center text-muted mt-1">Payload</div>
                            </div>
                        </div>
                         <div class="mt-2">
                            <?= Html::dropDownList('assign_slot_id', $currentSlotId ?? null, $slotList ?? [], [
                                'class' => 'form-select form-select-sm',
                                'prompt' => 'Select Yard Slot...'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="block block-rounded content-card shadow-sm">
            <div class="block-header block-header-default">
                <h3 class="block-title fw-bold">Condition Report</h3>
                <div class="block-options">
                     <span class="fs-sm text-muted">Inspector: </span>
                     <span class="badge bg-success"><?= Yii::$app->user->identity->username ?? 'Admin' ?></span>
                     <?= $form->field($survey, 'surveyor_name')->hiddenInput(['value' => Yii::$app->user->identity->username ?? 'Admin'])->label(false) ?>
                </div>
            </div>
            
            <div class="block-content">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Survey Date & Time</label>
                        <?= $form->field($survey, 'survey_date')->textInput(['type' => 'datetime-local', 'class' => 'form-control form-control-lg', 'value' => date('Y-m-d\TH:i')])->label(false) ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Evidence Photo</label>
                        <div class="input-group">
                            <?= $form->field($survey, 'survey_photo_file')->fileInput(['accept' => 'image/*', 'class' => 'form-control form-control-lg'])->label(false) ?>
                            <?php if ($survey->survey_photo_path): ?>
                                <a href="<?= Yii::getAlias('@web') . '/' . $survey->survey_photo_path ?>" target="_blank" class="btn btn-secondary"><i class="fa fa-eye"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-3 border rounded">
                    <table class="table table-bordered table-striped table-vcenter mb-0" id="damages-table">
                        <thead class="bg-body-dark text-white">
                            <tr class="text-uppercase fs-xs">
                                <th style="width: 18%;">Repair Code</th>
                                <th style="width: 25%;">Description</th>
                                <th style="width: 8%;" class="text-center">Qty</th>
                                <th style="width: 8%;" class="text-center">Hrs</th>
                                <th style="width: 12%;" class="text-end">Labor</th>
                                <th style="width: 12%;" class="text-end">Material</th>
                                <th style="width: 12%;" class="text-end">Total</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($damages as $i => $damage): ?>
                                <tr class="damage-row">
                                    <?php if (!$damage->isNewRecord) echo Html::activeHiddenInput($damage, "[$i]damage_id"); ?>

                                    <td>
                                        <?= $form->field($damage, "[$i]repair_code")->dropDownList($repairCodes, ['class' => 'form-select form-select-sm code-select', 'prompt' => 'Code'])->label(false) ?>
                                    </td>
                                    <td>
                                        <?= $form->field($damage, "[$i]description")->textInput(['class' => 'form-control form-control-sm desc-input bg-white', 'readonly' => true])->label(false) ?>
                                    </td>
                                    <td>
                                        <?= $form->field($damage, "[$i]quantity")->textInput(['class' => 'form-control form-control-sm qty-input text-center', 'type' => 'number', 'min' => 1])->label(false) ?>
                                    </td>
                                    <td>
                                        <?= $form->field($damage, "[$i]hours")->textInput(['class' => 'form-control form-control-sm hours-input calc-trigger text-center', 'type' => 'number', 'step' => '0.01'])->label(false) ?>
                                    </td>
                                    <td>
                                        <?= $form->field($damage, "[$i]labor_cost")->textInput(['class' => 'form-control form-control-sm labor-input calc-trigger text-end bg-white', 'readonly' => true])->label(false) ?>
                                    </td>
                                    <td>
                                        <?= $form->field($damage, "[$i]material_cost")->textInput(['class' => 'form-control form-control-sm material-input calc-trigger text-end bg-white', 'readonly' => false])->label(false) ?>
                                    </td>
                                    <td>
                                        <?= $form->field($damage, "[$i]total_cost")->textInput(['class' => 'form-control form-control-sm total-input fw-bold text-end bg-white', 'readonly' => true])->label(false) ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" title="Remove"><i class="fa fa-trash-alt"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-body-light">
                            <tr>
                                <td colspan="6" class="text-end fw-bold text-uppercase fs-sm pt-3">Estimate Total:</td>
                                <td class="text-end fw-bold fs-sm pt-3">
                                    KES <span id="grand-total-display">0.00</span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div id="sound-container-msg" class="alert alert-success d-flex align-items-center justify-content-center py-4 mb-3" style="display: none;">
                    <div class="text-center">
                        <div class="mb-2"><i class="fa fa-check-circle fa-3x"></i></div>
                        <h4 class="alert-heading fs-5 fw-bold mb-1">Container is Sound</h4>
                        <p class="mb-0 fs-sm text-muted">No damages recorded. Use the button below to add defects.</p>
                    </div>
                </div>
                
                <div class="mb-4 text-center">
                    <button type="button" class="btn btn-alt-primary rounded-pill px-4" id="add-damage-btn">
                        <i class="fa fa-plus-circle me-1"></i> Add Damage Line
                    </button>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">General Remarks / Observations</label>
                    <?= $form->field($survey, 'remarks')->textarea([
                        'rows' => 3, 
                        'class' => 'form-control',
                        'placeholder' => 'e.g. Needs cleaning, floor stained, door handle stiff...'
                    ])->label(false) ?>
                </div>

                <div class="alert alert-warning d-flex align-items-center justify-content-between mb-4 border-start border-4 border-warning">
                    <div>
                        <h5 class="alert-heading fw-bold fs-6 mb-1"><i class="fa fa-file-invoice-dollar me-1"></i> Billing Authorization</h5>
                        <div class="fs-xs text-muted">Enable this if the client is liable for the repairs.</div>
                    </div>
                    <div class="form-check form-switch">
                        <?= Html::hiddenInput('ContainerSurveys[bill_repairs]', 0) ?>
                        <?= $form->field($survey, 'bill_repairs')->checkbox(['class' => 'form-check-input fs-4', 'label' => false]) ?>
                    </div>
                </div>

                <div class="row pt-2 pb-4">
                    <div class="col-6">
                         <?= Html::a('<i class="fa fa-times me-1"></i> Cancel', ['index'], ['class' => 'btn btn-lg btn-alt-secondary px-4']) ?>
                    </div>
                    <div class="col-6 text-end">
                        <button type="submit" class="btn btn-lg btn-primary px-5 shadow fw-bold">
                            <i class="fa fa-save me-2"></i> Complete Survey
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

    function checkEmptyState() {
        if ($("#damages-table tbody tr").length === 0) {
            $("#damages-table").hide();
            $("#sound-container-msg").fadeIn();
        } else {
            $("#sound-container-msg").hide();
            $("#damages-table").fadeIn();
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

    $(document).on('input', '.calc-trigger, .qty-input', function() {
        updateRowTotal($(this).closest('tr'));
    });

    $("#add-damage-btn").click(function() {
        var tableBody = $("#damages-table tbody");
        
        if (tableBody.find("tr").length === 0) {
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