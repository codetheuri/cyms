<?php
use yii\helpers\Html;
use yii\helpers\Url;
use helpers\PermissionHelper;

$this->title = 'Dashboard';
?>

<?php if (!$hasDashboardAccess && empty($accessibleModules)): ?>
    <!-- No Access View -->
    <div class="content content-full text-center">
        <div class="py-5">
            <i class="fa fa-lock fa-4x text-muted mb-3"></i>
            <h1 class="h3 mb-2">Access Restricted</h1>
            <p class="fs-base fw-medium text-muted mb-4">
                You don't have permission to access any modules in the system.
            </p>
            <a class="btn btn-primary" href="<?= Url::to(['/site/logout']) ?>">
                <i class="fa fa-sign-out-alt me-1"></i> Logout
            </a>
        </div>
    </div>

<?php elseif (!$hasDashboardAccess && !empty($accessibleModules)): ?>
    <!-- User has access to modules but not dashboard widgets -->
    <div class="row">
        <div class="col-12">
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Welcome to <?= Yii::$app->name ?></h3>
                    <div class="block-options">
                        <span class="badge bg-primary"><?= Yii::$app->user->identity->username ?? 'User' ?></span>
                    </div>
                </div>
                <div class="block-content">
                    <p class="text-muted mb-4">You have access to the following modules:</p>
                    
                    <?php foreach ($accessibleModules as $category => $modules): ?>
                        <h4 class="border-bottom pb-2 mb-3"><?= $category ?></h4>
                        <div class="row">
                            <?php foreach ($modules as $module): ?>
                                <div class="col-md-4 col-lg-3 mb-4">
                                    <a class="block block-rounded block-link-pop text-center" href="<?= Url::to($module['url']) ?>">
                                        <div class="block-content block-content-full">
                                            <div class="py-3">
                                                <i class="fa fa-folder fa-2x text-primary"></i>
                                            </div>
                                            <p class="fw-semibold mb-0"><?= $module['label'] ?></p>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Full Dashboard View -->
    
    <!-- Conditional KPI Widgets -->
    <?php if (!empty($data)): ?>
    <div class="row">
        <?php if (isset($data['totalInYard'])): ?>
        <div class="col-6 col-md-3 col-lg-6 col-xl-3">
            <a class="block block-rounded block-link-pop border-start border-primary border-4" 
               href="<?= PermissionHelper::can('dashboard-yard-list') ? Url::to(['/dashboard/yard/index']) : 'javascript:void(0)' ?>">
                <div class="block-content block-content-full">
                    <div class="fs-sm fw-semibold text-uppercase text-muted">In Yard</div>
                    <div class="fs-2 fw-normal text-dark"><?= number_format($data['totalInYard']) ?></div>
                    <div class="fs-sm text-primary fw-bold mt-1"><i class="fa fa-box me-1"></i> TEUs</div>
                </div>
            </a>
        </div>
        <?php endif; ?>
        
        <?php if (isset($data['gateInToday']) || isset($data['gateOutToday'])): ?>
        <div class="col-6 col-md-3 col-lg-6 col-xl-3">
            <a class="block block-rounded block-link-pop border-start border-success border-4" 
               href="<?= PermissionHelper::canAny(['dashboard-visit-gate-in', 'dashboard-visit-gate-out']) ? Url::to(['/dashboard/visit/index']) : 'javascript:void(0)' ?>">
                <div class="block-content block-content-full">
                    <div class="fs-sm fw-semibold text-uppercase text-muted">Moves Today</div>
                    <div class="fs-2 fw-normal text-dark"><?= ($data['gateInToday'] ?? 0) + ($data['gateOutToday'] ?? 0) ?></div>
                    <div class="fs-sm text-success fw-bold mt-1">
                        <?php if (isset($data['gateInToday'])): ?>
                        <span class="me-2"><i class="fa fa-arrow-down"></i> <?= $data['gateInToday'] ?></span>
                        <?php endif; ?>
                        <?php if (isset($data['gateOutToday'])): ?>
                        <span><i class="fa fa-arrow-up"></i> <?= $data['gateOutToday'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>
        
        <?php if (isset($data['pendingSurveys'])): ?>
        <div class="col-6 col-md-3 col-lg-6 col-xl-3">
            <a class="block block-rounded block-link-pop border-start border-warning border-4" 
               href="<?= PermissionHelper::can('dashboard-survey-list') ? Url::to(['/dashboard/survey/index']) : 'javascript:void(0)' ?>">
                <div class="block-content block-content-full">
                    <div class="fs-sm fw-semibold text-uppercase text-muted">Pending Survey</div>
                    <div class="fs-2 fw-normal text-dark"><?= $data['pendingSurveys'] ?></div>
                    <div class="fs-sm text-warning fw-bold mt-1"><i class="fa fa-clock me-1"></i> Action Required</div>
                </div>
            </a>
        </div>
        <?php endif; ?>
        
        <?php if (isset($data['revenueMonth'])): ?>
        <div class="col-6 col-md-3 col-lg-6 col-xl-3">
            <a class="block block-rounded block-link-pop border-start border-info border-4" 
               href="<?= PermissionHelper::can('dashboard-billing-list') ? Url::to(['/dashboard/billing/index']) : 'javascript:void(0)' ?>">
                <div class="block-content block-content-full">
                    <div class="fs-sm fw-semibold text-uppercase text-muted">Revenue (Month)</div>
                    <div class="fs-2 fw-normal text-dark">
                        <span class="fs-4 align-top">KES</span> <?= number_format($data['revenueMonth']) ?>
                    </div>
                    <div class="fs-sm text-info fw-bold mt-1"><i class="fa fa-chart-line me-1"></i> Collections</div>
                </div>
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Charts Section -->
    <div class="row">
        <?php if (isset($charts['gateActivity'])): ?>
        <div class="col-xl-8">
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Gate Traffic Overview (Last 7 Days)</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-toggle="block-option" data-action="state_toggle" data-action-mode="demo">
                            <i class="si si-refresh"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content block-content-full text-center">
                    <div class="py-3" style="height: 300px; position: relative;">
                        <canvas id="gateChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($charts['stockByLine'])): ?>
        <div class="col-xl-4">
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Stock by Line</h3>
                </div>
                <div class="block-content block-content-full">
                    <div class="py-3" style="height: 300px; position: relative;">
                        <canvas id="stockChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Recent Lists Section -->
    <div class="row">
        <?php if (isset($recentLists['arrivals'])): ?>
        <div class="col-md-6">
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Recent Arrivals</h3>
                    <div class="block-options">
                        <?php if (PermissionHelper::can('dashboard-visit-gate-in')): ?>
                        <a href="<?= Url::to(['/dashboard/visit/index']) ?>" class="btn btn-sm btn-primary">
                            <i class="fa fa-list me-1"></i> View All
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="block-content">
                    <table class="table table-striped table-vcenter">
                        <thead>
                            <tr>
                                <th>Container</th>
                                <th>Time</th>
                                <th>Container Owner</th>
                                <th>Line</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentLists['arrivals'] as $visit): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?= $visit->container_number ?></td>
                                <td class="fs-sm"><?= Yii::$app->formatter->asRelativeTime($visit->created_at) ?></td>
                                <td class="fs-sm"><?= $visit->containerOwner->owner_name ?? '-' ?></td>
                                <td><span class="badge bg-info"><?= $visit->shippingLine->line_code ?? '-' ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($recentLists['paidInvoices'])): ?>
        <div class="col-md-6">
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Recent Completed Invoices</h3>
                    <div class="block-options">
                        <?php if (PermissionHelper::can('dashboard-billing-list')): ?>
                        <a href="<?= Url::to(['/dashboard/billing/index']) ?>" class="btn btn-sm btn-alt-primary">View All</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="block-content">
                     <table class="table table-striped table-vcenter">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Amount</th>
                                <th>Updated</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentLists['paidInvoices'] as $bill): ?>
                            <tr>
                                <td class="fw-bold"><?= $bill->invoice_number ?></td>
                                <td class="text-success fw-bold"><?= Yii::$app->formatter->asCurrency($bill->total_paid, 'KES') ?></td>
                                <td class="fs-sm"><?= Yii::$app->formatter->asDate($bill->updated_at) ?></td>
                                <td><span class="badge bg-success">PAID</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Show accessible modules even in full dashboard -->
    <?php if (!empty($accessibleModules)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Quick Access</h3>
                </div>
                <div class="block-content">
                    <div class="row">
                        <?php 
                        $count = 0;
                        foreach ($accessibleModules as $category => $modules): 
                            foreach ($modules as $module): 
                                if ($count < 8): // Limit to 8 quick access items
                        ?>
                        <div class="col-md-3 col-lg-2 mb-3">
                            <a class="btn btn-alt-primary w-100 text-start" href="<?= Url::to($module['url']) ?>">
                                <i class="fa fa-folder me-2"></i>
                                <?= $module['label'] ?>
                            </a>
                        </div>
                        <?php 
                                $count++;
                                endif;
                            endforeach;
                        endforeach; 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
<?php endif; ?>

<?php
// Register Chart.js only if needed
if (isset($charts['gateActivity']) || isset($charts['stockByLine'])) {
    $this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js', [
        'position' => \yii\web\View::POS_END
    ]);
}
?>

<?php if (isset($charts['gateActivity'])): ?>
<?php
$jsLabels = json_encode($charts['gateActivity']['labels'], JSON_UNESCAPED_UNICODE);
$jsDataIn = json_encode(array_map('intval', $charts['gateActivity']['dataIn']));
$jsDataOut = json_encode(array_map('intval', $charts['gateActivity']['dataOut']));

$gateChartScript = <<<JS
document.addEventListener('DOMContentLoaded', function () {
    // Gate Traffic Chart
    const ctx1 = document.getElementById('gateChart');
    if (ctx1) {
        new Chart(ctx1.getContext('2d'), {
            type: 'bar',
            data: {
                labels: $jsLabels,
                datasets: [
                    { 
                        label: 'Gate IN', 
                        data: $jsDataIn, 
                        backgroundColor: '#006D44', 
                        borderRadius: 4 
                    },
                    { 
                        label: 'Gate OUT', 
                        data: $jsDataOut, 
                        backgroundColor: '#dc3545', 
                        borderRadius: 4 
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: 'top' 
                    } 
                },
                scales: { 
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    } 
                }
            }
        });
    }
});
JS;

$this->registerJs($gateChartScript, \yii\web\View::POS_END);
?>
<?php endif; ?>

<?php if (isset($charts['stockByLine'])): ?>
<?php
$jsPieLabels = json_encode($charts['stockByLine']['labels']);
$jsPieData = json_encode(array_map('intval', $charts['stockByLine']['data']));

$stockChartScript = <<<JS
document.addEventListener('DOMContentLoaded', function () {
    // Stock by Line Chart
    const ctx2 = document.getElementById('stockChart');
    if (ctx2) {
        new Chart(ctx2.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: $jsPieLabels,
                datasets: [{
                    data: $jsPieData,
                    backgroundColor: ['#006D44', '#1C2536', '#ffc107', '#dc3545', '#17a2b8', '#6c757d'],
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: 'bottom',
                        labels: {
                            padding: 20
                        }
                    } 
                }
            }
        });
    }
});
JS;

$this->registerJs($stockChartScript, \yii\web\View::POS_END);
?>
<?php endif; ?>