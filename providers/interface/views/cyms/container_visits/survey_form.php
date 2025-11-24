<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $visit dashboard\models\ContainerVisits */
/* @var $survey dashboard\models\ContainerSurveys */
/* @var $damages dashboard\models\SurveyDamages[] */

$this->title = 'Perform Container Survey';
?>

<div class="block block-rounded content-card">
    <div class="block-header block-header-default">
        <h3 class="block-title"><?= $this->title ?>: <span class="text-primary"><?= $visit->container_number ?></span></h3>
        <div class="block-options">
            <?= Html::a('Back', ['index'], ['class' => 'btn btn-sm btn-alt-secondary']) ?>
        </div>
    </div>
    
    <div class="block-content block-content-full">
        
        <?php $form = ActiveForm::begin(['id' => 'dynamic-form']); ?>

        <div class="row mb-4">
            <div class="col-md-4">
                <label class="form-label">Container No</label>
                <input type="text" class="form-control fw-bold" value="<?= $visit->container_number ?>" readonly>
            </div>
            <div class="col-md-4">
                <?= $form->field($survey, 'surveyor_name')->textInput(['value' => Yii::$app->user->identity->username ?? 'System']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($survey, 'survey_date')->textInput(['type' => 'datetime-local', 'value' => date('Y-m-d\TH:i')]) ?>
            </div>
        </div>

        <h5 class="border-bottom pb-2 mb-3 text-danger"><i class="fa fa-exclamation-triangle me-2"></i> Damage Details</h5>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-vcenter" id="damages-table">
                <thead>
                    <tr class="bg-body-light">
                        <th style="width: 10%;">Code</th>
                        <th style="width: 30%;">Description</th>
                        <th style="width: 10%;">Qty</th>
                        <th style="width: 15%;">Labor ($)</th>
                        <th style="width: 15%;">Material ($)</th>
                        <th style="width: 15%;">Total ($)</th>
                        <th style="width: 5%;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($damages as $i => $damage): ?>
                        <tr class="damage-row">
                            <?php
                                // necessary for update action.
                                if (!$damage->isNewRecord) {
                                    echo Html::activeHiddenInput($damage, "[$i]damage_id");
                                }
                            ?>
                            
                            <td>
                                <?= $form->field($damage, "[$i]repair_code")->textInput(['class' => 'form-control form-control-sm', 'placeholder' => 'PN040'])->label(false) ?>
                            </td>
                            <td>
                                <?= $form->field($damage, "[$i]description")->textInput(['class' => 'form-control form-control-sm', 'placeholder' => 'Dent / Scratch'])->label(false) ?>
                            </td>
                            <td>
                                <?= $form->field($damage, "[$i]quantity")->textInput(['class' => 'form-control form-control-sm qty-input', 'type' => 'number'])->label(false) ?>
                            </td>
                            <td>
                                <?= $form->field($damage, "[$i]labor_cost")->textInput(['class' => 'form-control form-control-sm calc-input labor-input', 'type' => 'number', 'step' => '0.01'])->label(false) ?>
                            </td>
                            <td>
                                <?= $form->field($damage, "[$i]material_cost")->textInput(['class' => 'form-control form-control-sm calc-input material-input', 'type' => 'number', 'step' => '0.01'])->label(false) ?>
                            </td>
                            <td>
                                <?= $form->field($damage, "[$i]total_cost")->textInput(['class' => 'form-control form-control-sm total-input fw-bold', 'readonly' => true])->label(false) ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger remove-row-btn"><i class="fa fa-times"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7">
                            <button type="button" class="btn btn-sm btn-alt-primary" id="add-damage-btn">
                                <i class="fa fa-plus me-1"></i> Add Damage Row
                            </button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="row mt-4 pt-3 border-top">
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-check me-1"></i> Save Survey & Approve</button>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$script = <<< JS
$(document).ready(function() {
    var rowCount = $(".damage-row").length;

    // 1. ADD ROW
    $("#add-damage-btn").click(function() {
        var table = $("#damages-table tbody");
        var template = table.find("tr:first").clone();
        
        // Increment index for Yii2 loading
        template.find("input, select").each(function() {
            // Update name attribute: SurveyDamages[0][field] -> SurveyDamages[1][field]
            this.name = this.name.replace(/\[\d+\]/, "[" + rowCount + "]");
            this.id = this.id.replace(/-\d+-/, "-" + rowCount + "-");
            this.value = ""; // Clear value
            if($(this).hasClass('qty-input')) this.value = 1;
            if($(this).hasClass('calc-input')) this.value = 0;
        });
        
        // Remove the hidden ID input from the clone (so it's treated as new)
        template.find("input[type='hidden']").remove();

        // Remove any has-error classes
        template.find(".has-error").removeClass("has-error");
        template.find(".invalid-feedback").remove();

        table.append(template);
        rowCount++;
    });

    // 2. REMOVE ROW
    $(document).on("click", ".remove-row-btn", function() {
        if ($("#damages-table tbody tr").length > 1) {
            $(this).closest("tr").remove();
        } else {
            alert("At least one damage line is required (or leave empty if Sound).");
        }
    });

    // 3. AUTO-CALCULATE TOTALS
    $(document).on("input", ".calc-input, .qty-input", function() {
        var row = $(this).closest("tr");
        var labor = parseFloat(row.find(".labor-input").val()) || 0;
        var material = parseFloat(row.find(".material-input").val()) || 0;
        // var qty = parseFloat(row.find(".qty-input").val()) || 1; // Usually cost is per line, but enable if per qty
        
        var total = labor + material;
        row.find(".total-input").val(total.toFixed(2));
    });
});
JS;
$this->registerJs($script);
?>