<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use dashboard\models\ContainerVisits;
use dashboard\models\BillingPayments;
use dashboard\models\MasterShippingLines;

$this->title = 'Reports & Analytics';
$this->params['breadcrumbs'][] = $this->title;

// Dropdown Data
$shippingLines = ArrayHelper::map(MasterShippingLines::find()->orderBy('line_code')->all(), 'line_id', 'line_code');

// ... Stats Logic ...
$today = date('Y-m-d');
$movesToday = ContainerVisits::find()->where(['date_in' => $today])->orWhere(['date_out' => $today])->count();
$revenueMonth = BillingPayments::find()->where(['between', 'transaction_date', date('Y-m-01'), date('Y-m-t')])->sum('amount');
$adminEmail = Yii::$app->config->get('admin_email');
?>

<div class="row">
    
    <div class="col-lg-7 col-md-12">
        <div class="block block-rounded content-card h-100">
            <div class="block-header block-header-default">
                <h3 class="block-title"><i class="fa fa-chart-line me-2"></i> Business Reports</h3>
            </div>
            <div class="block-content">
                <?php $form = ActiveForm::begin(['action' => ['generate'], 'options' => ['target' => '_blank']]); ?>
                
                <div class="row">
                    <div class="col-12 mb-3">
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

                    <div class="col-md-6 mb-3" id="filter_line">
                        <label class="form-label">Filter by Shipping Line</label>
                        <?= Html::dropDownList('shipping_line_id', null, $shippingLines, ['class' => 'form-select', 'prompt' => 'All Lines']) ?>
                    </div>

                    <div class="col-md-6 mb-3" id="filter_move" style="display:none;">
                        <label class="form-label">Movement Direction</label>
                        <select class="form-select" name="move_type">
                            <option value="all">All Movements</option>
                            <option value="in">Gate IN Only</option>
                            <option value="out">Gate OUT Only</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3" id="filter_date">
                    <label class="form-label">Date Range</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="date_from" value="<?= date('Y-m-01') ?>">
                        <span class="input-group-text">to</span>
                        <input type="date" class="form-control" name="date_to" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Output Format</label>
                    <select class="form-select" name="format">
                        <option value="print">📄 PDF / Print View</option>
                        <option value="excel">📊 Excel Spreadsheet</option>
                    </select>
                </div>

                <hr>

                <div class="mb-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1" onclick="this.form.target='_blank'">
                        <i class="fa fa-file-export me-2"></i> Download Report
                    </button>
                    
                    <button type="submit" 
                            class="btn btn-outline-dark" 
                            formaction="<?= Url::to(['email-report']) ?>" 
                            onclick="this.form.target='_self'"
                            title="Send to <?= $adminEmail ?>">
                        <i class="fa fa-envelope me-2"></i> Email to Admin
                    </button>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5 col-md-12">
        <div class="row g-3">
            <div class="col-12">
                <div class="block block-rounded block-bordered p-3 d-flex align-items-center justify-content-between bg-body-light">
                    <div>
                        <div class="fs-4 fw-bold text-primary"><?= number_format($movesToday) ?></div>
                        <div class="text-muted text-uppercase fs-sm">Moves Today</div>
                    </div>
                    <i class="fa fa-truck-ramp-box fa-3x text-primary opacity-25"></i>
                </div>
            </div>
            <div class="col-12">
                <div class="block block-rounded block-bordered p-3 d-flex align-items-center justify-content-between bg-body-light">
                    <div>
                        <div class="fs-4 fw-bold text-success"><?= Yii::$app->formatter->asCurrency($revenueMonth ?? 0, 'KES') ?></div>
                        <div class="text-muted text-uppercase fs-sm">Revenue (Month)</div>
                    </div>
                    <i class="fa fa-coins fa-3x text-success opacity-25"></i>
                </div>
            </div>

            <div class="col-12 mt-4">
                <div class="block block-rounded content-card border-start border-3 border-warning">
                    <div class="block-header block-header-default">
                        <h3 class="block-title text-warning"><i class="fa fa-server me-2"></i> System Health</h3>
                    </div>
                    <div class="block-content">
                        <p class="fs-sm text-muted">
                            Generate a full SQL database dump and email it to <strong><?= $adminEmail ?? 'not configured' ?></strong> for disaster recovery.
                        </p>
                        
                        <div class="text-end mb-3">
                            <?= Html::a('<i class="fa fa-database me-2"></i> Backup Database Now', ['backup-database'], [
                                'class' => 'btn btn-warning w-100',
                                'data' => [
                                    'confirm' => 'This may take a few seconds depending on database size. Continue?',
                                    'method' => 'post',
                                ],
                            ]) ?>
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
        divLine.parentElement.style.display = 'block'; // Ensure parent col is visible
        divLine.style.display = 'block';
    } else {
        divLine.parentElement.style.display = 'none';
    }

    // Show Move Type only for Gate Moves
    if (type === 'gate_moves') {
        divMove.parentElement.style.display = 'block';
        divMove.style.display = 'block';
    } else {
        divMove.parentElement.style.display = 'none';
    }
    
    // Hide Dates for Stock & Debtors (Current Snapshot)
    if (['stock_list', 'debtors'].includes(type)) {
        divDate.style.display = 'none';
    } else {
        divDate.style.display = 'block';
    }
}
// Run on load
document.addEventListener("DOMContentLoaded", function() {
    toggleFilters();
});
</script>