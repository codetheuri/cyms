<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';
?>

<div class="row">
    <div class="col-6 col-md-3 col-lg-6 col-xl-3">
        <a class="block block-rounded block-link-pop border-start border-primary border-4" href="<?= Url::to(['/dashboard/visit/index']) ?>">
            <div class="block-content block-content-full">
                <div class="fs-sm fw-semibold text-uppercase text-muted">In Yard</div>
                <div class="fs-2 fw-normal text-dark"><?= number_format($totalInYard) ?></div>
                <div class="fs-sm text-primary fw-bold mt-1"><i class="fa fa-box me-1"></i> TEUs</div>
            </div>
        </a>
    </div>
    
    <div class="col-6 col-md-3 col-lg-6 col-xl-3">
        <a class="block block-rounded block-link-pop border-start border-success border-4" href="javascript:void(0)">
            <div class="block-content block-content-full">
                <div class="fs-sm fw-semibold text-uppercase text-muted">Moves Today</div>
                <div class="fs-2 fw-normal text-dark"><?= $gateInToday + $gateOutToday ?></div>
                <div class="fs-sm text-success fw-bold mt-1">
                    <span class="me-2"><i class="fa fa-arrow-down"></i> <?= $gateInToday ?></span>
                    <span><i class="fa fa-arrow-up"></i> <?= $gateOutToday ?></span>
                </div>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-3 col-lg-6 col-xl-3">
        <a class="block block-rounded block-link-pop border-start border-warning border-4" href="<?= Url::to(['/dashboard/visit/index']) ?>">
            <div class="block-content block-content-full">
                <div class="fs-sm fw-semibold text-uppercase text-muted">Pending Survey</div>
                <div class="fs-2 fw-normal text-dark"><?= $pendingSurveys ?></div>
                <div class="fs-sm text-warning fw-bold mt-1"><i class="fa fa-clock me-1"></i> Action Required</div>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-3 col-lg-6 col-xl-3">
        <a class="block block-rounded block-link-pop border-start border-info border-4" href="<?= Url::to(['/dashboard/billing/index']) ?>">
            <div class="block-content block-content-full">
                <div class="fs-sm fw-semibold text-uppercase text-muted">Revenue (Month)</div>
                <div class="fs-2 fw-normal text-dark">
                    <span class="fs-4 align-top">KES</span> <?= number_format($revenueMonth) ?>
                </div>
                <div class="fs-sm text-info fw-bold mt-1"><i class="fa fa-chart-line me-1"></i> Collections</div>
            </div>
        </a>
    </div>
</div>

<div class="row">
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
</div>

<div class="row">
    <div class="col-md-6">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Recent Arrivals</h3>
                <div class="block-options">
                    <a href="<?= Url::to(['/dashboard/visit/gate-in']) ?>" class="btn btn-sm btn-primary">
                        <i class="fa fa-plus me-1"></i> Gate IN
                    </a>
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
                        <?php foreach($recentIn as $visit): ?>
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

    <div class="col-md-6">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Recent Completed Invoices</h3>
                <div class="block-options">
                    <a href="<?= Url::to(['/dashboard/billing/index']) ?>" class="btn btn-sm btn-alt-primary">View All</a>
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
                        <?php foreach($recentPaid as $bill): ?>
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
</div>

<?php
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js', [
    'position' => \yii\web\View::POS_END
]);

$jsLabels     = json_encode($chartLabels, JSON_UNESCAPED_UNICODE);
$jsDataIn     = json_encode(array_map('intval', $chartDataIn));
$jsDataOut    = json_encode(array_map('intval', $chartDataOut));
$jsPieLabels  = json_encode($pieLabels);
$jsPieData    = json_encode(array_map('intval', $pieData));

$script = <<<JS
document.addEventListener('DOMContentLoaded', function () {
    console.log('Chart data loaded:', { labels: $jsLabels, in: $jsDataIn, out: $jsDataOut, pieLabels: $jsPieLabels, pieData: $jsPieData });

    // Gate Traffic Chart
    const ctx1 = document.getElementById('gateChart');
    if (ctx1) {
        new Chart(ctx1.getContext('2d'), {
            type: 'bar',
            data: {
                labels: $jsLabels,
                datasets: [
                    { label: 'Gate IN', data: $jsDataIn, backgroundColor: '#006D44', borderRadius: 4 },
                    { label: 'Gate OUT', data: $jsDataOut, backgroundColor: '#dc3545', borderRadius: 4 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

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
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
});
JS;

$this->registerJs($script, \yii\web\View::POS_END);
?>