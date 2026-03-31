<?php

use helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var array $summary */
/** @var array $breakdown */
/** @var string $from */
/** @var string $to */

$this->title = 'Net Profit & Economic Summary';
$this->params['breadcrumbs'][] = ['label' => 'Expenses', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Summary';

$profit = $summary['profit'];
$profitColor = ($profit >= 0) ? 'success' : 'danger';
$profitIcon = ($profit >= 0) ? 'arrow-trend-up' : 'arrow-trend-down';
?>

<div class="row">
    <!-- 1. DATE FILTERS -->
    <div class="col-md-12 mb-4">
        <div class="block block-rounded bg-body-extra-light shadow-sm">
            <div class="block-content block-content-full">
                <form method="get" class="row align-items-center">
                    <div class="col-md-3">
                        <label class="form-label fs-xs text-uppercase text-muted fw-bold">Period From</label>
                        <input type="date" name="from" class="form-control" value="<?= Html::encode($from) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-xs text-uppercase text-muted fw-bold">To Date</label>
                        <input type="date" name="to" class="form-control" value="<?= Html::encode($to) ?>">
                    </div>
                    <div class="col-md-3 mt-4">
                        <button type="submit" class="btn btn-alt-primary w-100 fw-bold">
                            <i class="fa fa-sync me-1"></i> Regenerate Analysis
                        </button>
                    </div>
                    <div class="col-md-3 mt-4">
                        <a href="<?= Url::current(['export' => 'excel']) ?>" class="btn btn-alt-success w-100 fw-bold">
                            <i class="fa fa-file-excel me-1"></i> Export To Excel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 2. HIGH LEVEL METRICS -->
    <div class="col-md-4">
        <div class="block block-rounded d-flex flex-column h-100 mb-0 shadow-sm border-start border-4 border-success">
            <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                <div>
                   <p class="fs-sm fw-semibold text-uppercase text-muted mb-0">Operational Income</p>
                   <p class="fs-2 fw-bold text-dark mb-0">KES <?= number_format($summary['income'], 2) ?></p>
                   <small class="text-success"><i class="fa fa-circle-check me-1"></i> Total Collections Identified</small>
                </div>
                <div class="item item-rounded bg-success-light">
                   <i class="fa fa-money-bill-wave text-success fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="block block-rounded d-flex flex-column h-100 mb-0 shadow-sm border-start border-4 border-danger">
            <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                <div>
                   <p class="fs-sm fw-semibold text-uppercase text-muted mb-0">Yard Expenses</p>
                   <p class="fs-2 fw-bold text-dark mb-0">KES <?= number_format($summary['expenses'], 2) ?></p>
                   <small class="text-danger"><i class="fa fa-circle-minus me-1"></i> Total Outgoings Logic</small>
                </div>
                <div class="item item-rounded bg-danger-light">
                   <i class="fa fa-hand-holding-dollar text-danger fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="block block-rounded d-flex flex-column h-100 mb-0 shadow-sm border-start border-4 border-<?= $profitColor ?>">
            <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                <div>
                   <p class="fs-sm fw-semibold text-uppercase text-muted mb-0">Estimated Net Profit</p>
                   <p class="fs-2 fw-bold text-<?= $profitColor ?> mb-0">KES <?= number_format($profit, 2) ?></p>
                   <small class="text-<?= $profitColor ?>"><i class="fa fa-<?= $profitIcon ?> me-1"></i> Analysis Results</small>
                </div>
                <div class="item item-rounded bg-<?= $profitColor ?>-light">
                   <i class="fa fa-chart-line text-<?= $profitColor ?> fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. DETAILED BREAKDOWN -->
    <div class="col-md-8 mt-4">
        <div class="block block-rounded shadow-sm">
            <div class="block-header block-header-default">
                <h3 class="block-title fw-bold">Spending Allocation (By Category)</h3>
            </div>
            <div class="block-content">
                <div class="table-responsive">
                    <table class="table table-striped table-vcenter">
                        <thead>
                            <tr class="fs-sm">
                                <th>Category Name</th>
                                <th class="text-end" style="width: 200px;">Total Expenditure</th>
                                <th class="text-center" style="width: 150px;">% of Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($breakdown)): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">No expenses found for this period.</td></tr>
                            <?php else: ?>
                                <?php foreach ($breakdown as $item): 
                                    $percentage = ($summary['expenses'] > 0) ? ($item['total_amount'] / $summary['expenses']) * 100 : 0;
                                ?>
                                    <tr>
                                        <td class="fw-bold"><?= Html::encode($item['category_name']) ?></td>
                                        <td class="text-end text-danger fw-semibold">KES <?= number_format($item['total_amount'], 2) ?></td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percentage ?>%;"></div>
                                            </div>
                                            <small class="fw-bold"><?= number_format($percentage, 1) ?>%</small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <td class="fw-bold">TOTAL AGGREGATED EXPENSE</td>
                                <td class="text-end fw-bold">KES <?= number_format($summary['expenses'], 2) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mt-4">
         <div class="block block-rounded bg-primary shadow-sm h-100 d-flex flex-column justify-content-center px-4 py-5 text-center alert alert-info mb-0">
             <i class="fa fa-shield-halved fa-4x text-white-50 mb-4"></i>
             <h4 class="text-white fw-bold mb-2">Integrated Ledger System</h4>
             <p class="text-white-75 fs-sm">These figures are calculated live by auditing all confirmed billing payments against recorded yard expenses. Always verify bank statements before final tax filing.</p>
         </div>
    </div>
</div>
