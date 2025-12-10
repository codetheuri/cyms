<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use dashboard\models\ContainerVisits;
use dashboard\models\BillingPayments;
use dashboard\models\MasterShippingLines;

$this->title = 'Reports & Analytics';
$this->params['breadcrumbs'][] = $this->title;

// Dropdown Data
$shippingLines = ArrayHelper::map(MasterShippingLines::find()->orderBy('line_code')->all(), 'line_id', 'line_code');

// ... Stats Logic (same as before) ...
$today = date('Y-m-d');
$movesToday = ContainerVisits::find()->where(['date_in' => $today])->orWhere(['date_out' => $today])->count();
$revenueMonth = BillingPayments::find()->where(['between', 'transaction_date', date('Y-m-01'), date('Y-m-t')])->sum('amount');
?>

<div class="row">
    <div class="col-md-5">
        <div class="block block-rounded content-card">
            <div class="block-header block-header-default">
                <h3 class="block-title">Generate Report</h3>
            </div>
            <div class="block-content">
                <?php $form = ActiveForm::begin(['action' => ['generate'], 'options' => ['target' => '_blank']]); ?>
                
                <div class="mb-3">
                    <label class="form-label">Report Category</label>
                    <select class="form-select form-control-lg" name="report_type" id="report_type" onchange="toggleFilters()">
                        <optgroup label="Operations">
                            <option value="gate_moves">📅 Gate Activity</option>
                            <option value="stock_list">📦 Yard Stock List</option>
                            <option value="aging">⏳ Aging Report (>30 Days)</option>
                        </optgroup>
                        <optgroup label="Finance">
                            <option value="payments">💵 Payment Collections</option>
                            <option value="invoices">🧾 Invoices Raised</option>
                            <option value="debtors">⚠️ Outstanding Debtors</option>
                            <option value="repairs">🔧 Repair Revenue</option>
                        </optgroup>
                    </select>
                </div>

                <div class="mb-3" id="filter_line">
                    <label class="form-label">Filter by Shipping Line</label>
                    <?= Html::dropDownList('shipping_line_id', null, $shippingLines, ['class' => 'form-select', 'prompt' => 'All Lines']) ?>
                </div>

                <div class="mb-3" id="filter_move" style="display:none;">
                    <label class="form-label">Movement Direction</label>
                    <select class="form-select" name="move_type">
                        <option value="all">All Movements (In & Out)</option>
                        <option value="in">Gate IN Only</option>
                        <option value="out">Gate OUT Only</option>
                    </select>
                </div>

                <div class="mb-3" id="filter_date">
                    <label class="form-label">Date Range</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="date_from" value="<?= date('Y-m-01') ?>">
                        <span class="input-group-text">to</span>
                        <input type="date" class="form-control" name="date_to" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Format</label>
                    <select class="form-select" name="format">
                        <option value="print">📄 Print / PDF</option>
                        <option value="excel">📊 Excel</option>
                    </select>
                </div>

                <div class="mb-4">
                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="fa fa-file-export me-2"></i> Generate Report
                    </button>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="block block-rounded content-card h-100">
            <div class="block-header block-header-default">
                <h3 class="block-title">Quick Stats</h3>
            </div>
            <div class="block-content">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="block block-rounded block-bordered p-3 text-center bg-body-light h-100">
                            <i class="fa fa-truck-ramp-box fa-3x text-primary mb-2"></i>
                            <div class="fs-4 fw-bold"><?= number_format($movesToday) ?></div>
                            <div class="text-muted text-uppercase fs-sm">Moves Today</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="block block-rounded block-bordered p-3 text-center bg-body-light h-100">
                            <i class="fa fa-coins fa-3x text-success mb-2"></i>
                            <div class="fs-4 fw-bold"><?= Yii::$app->formatter->asCurrency($revenueMonth ?? 0, 'KES') ?></div>
                            <div class="text-muted text-uppercase fs-sm">Revenue (Month)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFilters() {
    var type = document.getElementById('report_type').value;
    var divLine = document.getElementById('filter_line');
    var divMove = document.getElementById('filter_move');
    var divDate = document.getElementById('filter_date');

    // Show Line filter for Ops reports
    if (['gate_moves', 'stock_list', 'aging'].includes(type)) {
        divLine.style.display = 'block';
    } else {
        divLine.style.display = 'none';
    }

    // Show Move Type only for Gate Moves
    if (type === 'gate_moves') {
        divMove.style.display = 'block';
    } else {
        divMove.style.display = 'none';
    }
    
    // Hide Dates for Stock & Debtors (Current Snapshot)
    if (['stock_list', 'debtors'].includes(type)) {
        divDate.style.display = 'none';
    } else {
        divDate.style.display = 'block';
    }
}
// Run on load to set initial state
toggleFilters();
</script>