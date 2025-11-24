<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use dashboard\models\ContainerVisits;
use dashboard\models\BillingPayments;

$this->title = 'Reports & Analytics';
$this->params['breadcrumbs'][] = $this->title;

// --- QUICK STATS ---
$today = date('Y-m-d');
$movesToday = ContainerVisits::find()
    ->where(['date_in' => $today])
    ->orWhere(['date_out' => $today])
    ->count();

$revenueMonth = BillingPayments::find()
    ->where(['between', 'transaction_date', date('Y-m-01'), date('Y-m-t')])
    ->sum('amount');
?>

<div class="row">
    <!-- 1. REPORT GENERATOR FORM -->
    <div class="col-md-5">
        <div class="block block-rounded content-card">
            <div class="block-header block-header-default">
                <h3 class="block-title">Generate Report</h3>
            </div>
            <div class="block-content">
                <?php $form = ActiveForm::begin(['action' => ['generate'], 'options' => ['target' => '_blank']]); ?>
                
                <div class="mb-4">
                    <label class="form-label">Select Report Type</label>
                    <select class="form-select form-control-lg" name="report_type" required>
                        <optgroup label="Operational Reports">
                            <option value="gate_moves">📅 Gate Moves (In/Out)</option>
                            <option value="stock_list">📦 Current Stock List (Yard)</option>
                            <option value="aging">⏳ Aging Report (> 30 Days)</option>
                        </optgroup>
                        <optgroup label="Financial Reports">
                            <option value="payments">💵 Payment Collections (Cash Book)</option>
                            <option value="invoices">🧾 Invoices Raised</option>
                            <option value="debtors">⚠️ Outstanding Debtors</option>
                            <option value="repairs">🔧 Repair Revenue</option>
                        </optgroup>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label">Date Range</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="date_from" value="<?= date('Y-m-01') ?>">
                        <span class="input-group-text">to</span>
                        <input type="date" class="form-control" name="date_to" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-text text-muted">Ignored for Stock List & Debtors.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Export Format</label>
                    <select class="form-select" name="format">
                        <option value="print">📄 View / Print (PDF)</option>
                        <option value="excel">📊 Export to Excel</option>
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

    <!-- 2. QUICK ANALYTICS TILES -->
    <div class="col-md-7">
        <div class="block block-rounded content-card h-100">
            <div class="block-header block-header-default">
                <h3 class="block-title">Live Overview</h3>
            </div>
            <div class="block-content">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="block block-rounded block-bordered p-3 text-center bg-body-light h-100">
                            <i class="fa fa-truck-ramp-box fa-3x text-primary mb-2"></i>
                            <div class="fs-4 fw-bold"><?= number_format($movesToday) ?></div>
                            <div class="text-muted text-uppercase fs-sm">Gate Moves Today</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="block block-rounded block-bordered p-3 text-center bg-body-light h-100">
                            <i class="fa fa-coins fa-3x text-success mb-2"></i>
                            <div class="fs-4 fw-bold"><?= Yii::$app->formatter->asCurrency($revenueMonth ?? 0, 'KES') ?></div>
                            <div class="text-muted text-uppercase fs-sm">Collections (This Month)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>