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

// --- DATA FETCHING ---
$shippingLines = ArrayHelper::map(MasterShippingLines::find()->orderBy('line_code')->all(), 'line_id', 'line_code');

// Stats Logic
$today = date('Y-m-d');
$movesToday = ContainerVisits::find()
    ->where(['date_in' => $today])
    ->orWhere(['date_out' => $today])
    ->count();

// Force float and default to 0
$revenueMonth = (float) BillingPayments::find()
    ->where(['between', 'transaction_date', date('Y-m-01'), date('Y-m-t')])
    ->sum('amount') ?: 0.00;

$adminEmail = Yii::$app->config->get('admin_email');
?>

<div class="row g-4">
    <div class="col-lg-4 col-md-5">
        <div class="block block-rounded h-100">
            <div class="block-header block-header-default">
                <h3 class="block-title fw-bold text-uppercase fs-sm">1. Select Report</h3>
            </div>
            <div class="block-content p-0">
                <div class="list-group list-group-flush" id="report-menu">
                    
                    <div class="p-3 bg-body-light fw-bold text-xs text-uppercase text-muted">Operations</div>
                    
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex align-items-center p-3 active-report" 
                       onclick="selectReport('gate_moves', this)" data-filters="dates,lines,moves">
                        <div class="btn btn-icon btn-sm btn-alt-primary rounded-circle me-3"><i class="fa fa-truck-moving"></i></div>
                        <div>
                            <div class="fw-bold">Gate Activity</div>
                            <div class="fs-xs text-muted">In/Out Logs & History</div>
                        </div>
                    </a>

                    <a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex align-items-center p-3" 
                       onclick="selectReport('stock_list', this)" data-filters="lines">
                        <div class="btn btn-icon btn-sm btn-alt-info rounded-circle me-3"><i class="fa fa-cubes"></i></div>
                        <div>
                            <div class="fw-bold">Yard Stock List</div>
                            <div class="fs-xs text-muted">Current Inventory Snapshot</div>
                        </div>
                    </a>

                    <a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex align-items-center p-3" 
                       onclick="selectReport('aging', this)" data-filters="lines">
                        <div class="btn btn-icon btn-sm btn-alt-danger rounded-circle me-3"><i class="fa fa-hourglass-half"></i></div>
                        <div>
                            <div class="fw-bold">Aging Report</div>
                            <div class="fs-xs text-muted">Overstaying Containers (>30 Days)</div>
                        </div>
                    </a>

                    <div class="p-3 bg-body-light fw-bold text-xs text-uppercase text-muted border-top">Finance</div>

                    <a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex align-items-center p-3" 
                       onclick="selectReport('credit_containers', this)" data-filters="dates,lines">
                        <div class="btn btn-icon btn-sm btn-alt-warning rounded-circle me-3"><i class="fa fa-hand-holding-usd"></i></div>
                        <div>
                            <div class="fw-bold">Credit Releases</div>
                            <div class="fs-xs text-muted">Containers released on credit</div>
                        </div>
                    </a>

                    <a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex align-items-center p-3" 
                       onclick="selectReport('payments', this)" data-filters="dates">
                        <div class="btn btn-icon btn-sm btn-alt-success rounded-circle me-3"><i class="fa fa-money-bill-wave"></i></div>
                        <div>
                            <div class="fw-bold">Payment Collections</div>
                            <div class="fs-xs text-muted">Daily/Monthly Receipts</div>
                        </div>
                    </a>

                    <a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex align-items-center p-3" 
                       onclick="selectReport('invoices', this)" data-filters="dates">
                        <div class="btn btn-icon btn-sm btn-alt-warning rounded-circle me-3"><i class="fa fa-file-invoice"></i></div>
                        <div>
                            <div class="fw-bold">Invoices Raised</div>
                            <div class="fs-xs text-muted">Billing Generation Log</div>
                        </div>
                    </a>

                    <a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex align-items-center p-3" 
                       onclick="selectReport('debtors', this)" data-filters="none">
                        <div class="btn btn-icon btn-sm btn-alt-danger rounded-circle me-3"><i class="fa fa-exclamation-triangle"></i></div>
                        <div>
                            <div class="fw-bold">Outstanding Debtors</div>
                            <div class="fs-xs text-muted">Unpaid Bills Summary</div>
                        </div>
                    </a>
                    
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex align-items-center p-3" 
                       onclick="selectReport('repairs', this)" data-filters="dates">
                        <div class="btn btn-icon btn-sm btn-alt-secondary rounded-circle me-3"><i class="fa fa-wrench"></i></div>
                        <div>
                            <div class="fw-bold">Repair Revenue</div>
                            <div class="fs-xs text-muted">Repair Costs Summary</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 col-md-7">
        <?php $form = ActiveForm::begin(['action' => ['generate'], 'options' => ['target' => '_blank', 'id' => 'report-form']]); ?>
        
        <input type="hidden" name="report_type" id="report_type_input" value="gate_moves">

        <div class="block block-rounded mb-4">
            <div class="block-header block-header-default bg-body-light">
                <h3 class="block-title fw-bold" id="config-title">
                    <i class="fa fa-sliders-h me-2 text-muted"></i> Configure Report
                </h3>
            </div>
            
            <div class="block-content">
                <div class="row g-4">
                    
                    <div class="col-md-12 filter-section" id="filter-dates">
                        <label class="form-label text-uppercase text-xs fw-bold text-muted">Date Range</label>
                        <div class="input-group input-group-lg">
                            <input type="date" class="form-control" name="date_from" value="<?= date('Y-m-01') ?>">
                            <span class="input-group-text bg-body-light fw-bold">TO</span>
                            <input type="date" class="form-control" name="date_to" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="col-md-6 filter-section" id="filter-lines">
                        <label class="form-label text-uppercase text-xs fw-bold text-muted">Shipping Line</label>
                        <?= Html::dropDownList('shipping_line_id', null, $shippingLines, [
                            'class' => 'form-select form-select-lg', 
                            'prompt' => 'All Lines'
                        ]) ?>
                    </div>

                    <div class="col-md-6 filter-section" id="filter-moves">
                        <label class="form-label text-uppercase text-xs fw-bold text-muted">Movement Direction</label>
                        <select class="form-select form-select-lg" name="move_type">
                            <option value="all">All Movements (In & Out)</option>
                            <option value="in">Gate IN Only</option>
                            <option value="out">Gate OUT Only</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <hr class="my-2">
                        <label class="form-label text-uppercase text-xs fw-bold text-muted mt-2">Output Format</label>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="block block-rounded block-bordered block-link-shadow cursor-pointer mb-0 h-100">
                                    <input type="radio" class="btn-check" name="format" value="print" checked>
                                    <div class="block-content p-3 text-center btn-outline-secondary rounded h-100 d-flex flex-column justify-content-center">
                                        <i class="fa fa-file-pdf fa-2x text-danger mb-2"></i>
                                        <div class="fw-bold fs-sm">PDF / Print</div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6">
                                <label class="block block-rounded block-bordered block-link-shadow cursor-pointer mb-0 h-100">
                                    <input type="radio" class="btn-check" name="format" value="excel">
                                    <div class="block-content p-3 text-center btn-outline-success rounded h-100 d-flex flex-column justify-content-center">
                                        <i class="fa fa-file-excel fa-2x text-success mb-2"></i>
                                        <div class="fw-bold fs-sm">Excel Export</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-4 pb-4">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm" onclick="this.form.target='_blank'">
                                <i class="fa fa-download me-2"></i> Generate Report
                            </button>
                            
                            <button type="submit" 
                                    class="btn btn-alt-secondary" 
                                    formaction="<?= Url::to(['email-report']) ?>" 
                                    onclick="this.form.target='_self'">
                                <i class="fa fa-envelope me-2"></i> Email Report to Admin
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
        
        <div class="row g-3 mb-4">
             <div class="col-md-6">
                <div class="block block-rounded bg-white border border-2 border-primary mb-0 h-100">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between p-3">
                        <div class="me-3">
                            <div class="fs-xs fw-bold text-muted text-uppercase mb-1">Moves Today</div>
                            <div class="fs-2 fw-bold text-primary"><?= number_format($movesToday) ?></div>
                        </div>
                        <i class="fa fa-truck-moving text-primary opacity-25 fa-3x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="block block-rounded bg-white border border-2 border-success mb-0 h-100">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between p-3">
                        <div class="me-3">
                            <div class="fs-xs fw-bold text-muted text-uppercase mb-1">Month Revenue</div>
                            <div class="fs-2 fw-bold text-success"><?= Yii::$app->formatter->asCurrency($revenueMonth, 'KES') ?></div>
                        </div>
                        <i class="fa fa-coins text-success opacity-25 fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="block block-rounded border-start border-4 border-warning mb-0">
            <div class="block-header block-header-default">
                <h3 class="block-title text-warning-dark fw-bold"><i class="fa fa-server me-2"></i> System Health & Backup</h3>
            </div>
            <div class="block-content">
                <div class="row align-items-center py-2">
                    <div class="col-md-8">
                         <p class="fs-sm text-muted mb-0">
                            Generate a full SQL database dump and email it to <strong><?= $adminEmail ?? 'admin' ?></strong> for disaster recovery purposes.
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <?= Html::a('<i class="fa fa-database me-2"></i> Backup Now', ['backup-database'], [
                            'class' => 'btn btn-warning w-100 shadow-sm',
                            'data' => [
                                'confirm' => 'This process may take a few seconds. Continue?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    /* Custom Active State for Left Menu */
    .active-report {
        background-color: #f0f9ff !important; /* Light Blue */
        border-left: 4px solid #007bff !important;
        color: #007bff !important;
        transition: all 0.2s;
    }
    .active-report .text-muted {
        color: #6c757d !important;
    }
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
    .cursor-pointer { cursor: pointer; }
    
    /* Radio Button Visuals */
    .btn-check:checked + .block-content {
        background-color: #f6f7f9;
        box-shadow: 0 0 0 2px #0665d0 inset;
    }
</style>

<script>
function selectReport(type, element) {
    // 1. Update Hidden Input
    document.getElementById('report_type_input').value = type;

    // 2. Visual: Highlight Active Menu
    document.querySelectorAll('#report-menu a').forEach(el => el.classList.remove('active-report'));
    element.classList.add('active-report');

    // 3. Update Title
    var reportName = element.querySelector('.fw-bold').innerText;
    document.getElementById('config-title').innerHTML = '<i class="fa fa-sliders-h me-2 text-muted"></i> Configure: ' + reportName;

    // 4. Show/Hide Filters based on Data Attribute
    var requiredFilters = element.getAttribute('data-filters') || '';
    
    // Reset all (hide)
    ['filter-dates', 'filter-lines', 'filter-moves'].forEach(id => {
        document.getElementById(id).style.display = 'none';
    });

    // Show required
    if (requiredFilters.includes('dates')) document.getElementById('filter-dates').style.display = 'block';
    if (requiredFilters.includes('lines')) document.getElementById('filter-lines').style.display = 'block';
    if (requiredFilters.includes('moves')) document.getElementById('filter-moves').style.display = 'block';
}

// Initialize on Load (Highlight first item)
document.addEventListener("DOMContentLoaded", function() {
    var defaultItem = document.querySelector('.active-report');
    if(defaultItem) {
        selectReport('gate_moves', defaultItem);
    }
});
</script>