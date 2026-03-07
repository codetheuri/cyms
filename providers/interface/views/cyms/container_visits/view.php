<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model dashboard\models\ContainerVisits */

$this->title = $model->is_truck_only ? 'Truck Only' : $model->container_number;
$this->params['breadcrumbs'][] = ['label' => 'Gate Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// --- 1. STATUS LOGIC ---
$statusClass = match ($model->status) {
    'IN_YARD' => 'bg-warning',
    'SURVEYED' => 'bg-info',
    'GATE_OUT' => 'bg-secondary',
    default => 'bg-primary'
};

// --- 2. DAYS CALCULATION (Live View) ---
// If it's still in the yard, calculate days live so users see "5 Days" even if stored as 0 until checkout.
$daysInYard = $model->storage_days;
if ($model->status !== 'GATE_OUT' && $model->date_in) {
    $start = strtotime($model->date_in . ' ' . ($model->time_in ?: '00:00:00'));
    $diff = time() - $start;
    $daysInYard = ($diff < 0) ? 1 : (floor($diff / 86400) + 1);
}

// --- 3. DATE FORMATTER HELPER ---
$formatDateTime = function ($date, $time) {
    if (!$date) return '<span class="text-muted">-</span>';
    $d = Yii::$app->formatter->asDate($date, 'php:d M Y');
    $t = $time ? date('H:i', strtotime($time)) : '00:00';
    return "<div class='fw-bold'>$d</div><div class='fs-sm text-muted'><i class='fa fa-clock me-1'></i>$t hrs</div>";
};
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="content-heading d-flex align-items-center mt-0 pt-0 border-0">
        <span class="text-muted fw-light me-2"><?= $model->is_truck_only ? 'Truck Unit:' : 'Container:' ?></span>
        <span class="fw-bold"><?= $model->is_truck_only ? '<span class="badge bg-secondary">Truck Only</span>' : Html::encode($model->container_number) ?></span>
    </h2>
    <div>
        <?= Html::a('<i class="fa fa-arrow-left me-1"></i> Back', ['index'], ['class' => 'btn btn-sm btn-alt-secondary']) ?>

        <?php if ($model->status === 'SURVEYED' || $model->status === 'GATE_OUT'): ?>
            <a href="<?= Url::to(['/dashboard/reports/inward', 'id' => $model->visit_id]) ?>" target="_blank" class="btn btn-sm btn-alt-primary ms-1">
                <i class="fa fa-print me-1"></i> Inward
            </a>
        <?php endif; ?>

        <?php if ($model->status === 'GATE_OUT'): ?>
            <a href="<?= Url::to(['/dashboard/reports/outward', 'id' => $model->visit_id]) ?>" target="_blank" class="btn btn-sm btn-alt-danger ms-1">
                <i class="fa fa-print me-1"></i> Outward
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="block block-rounded block-link-shadow h-100 text-center d-flex align-items-center justify-content-center p-3 border-start border-5 border-<?= str_replace('bg-', '', $statusClass) ?>">
            <div>
                <div class="fs-xs fw-bold text-uppercase text-muted mb-1">Current Status</div>
                <span class="badge <?= $statusClass ?> fs-sm px-3 py-2 rounded-pill"><?= $model->status ?></span>
                <div class="fs-xs text-muted mt-2">Ticket: <?= $model->ticket_no_in ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="block block-rounded block-link-shadow h-100 text-center d-flex align-items-center justify-content-center p-3">
            <div>
                <div class="fs-xs fw-bold text-uppercase text-muted mb-1">Time in Yard</div>
                <div class="fs-2 fw-bold text-dark"><?= $daysInYard ?></div>
                <div class="fs-xs text-muted text-uppercase">Days Total</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <?php
        // Quick check on bill status
        $bill = \dashboard\models\BillingRecords::findOne(['visit_id' => $model->visit_id]);
        $billStatus = $bill ? $bill->status : 'UNBILLED';
        $billColor = match ($billStatus) {
            'PAID' => 'text-success',
            'CREDIT' => 'text-info',
            default => 'text-danger'
        };
        ?>
        <?php if ($model->is_truck_only): ?>
            <div class="block block-rounded block-link-shadow h-100 text-center d-flex align-items-center justify-content-center p-3">
                <div>
                    <div class="fs-xs fw-bold text-uppercase text-muted mb-1">Billing Status</div>
                    <div class="fs-3 fw-bold text-muted"><i class="fa fa-ban"></i></div>
                    <div class="fs-sm fw-bold text-muted mt-1">N/A</div>
                </div>
            </div>
        <?php else: ?>
            <a href="<?= $bill ? Url::to(['/dashboard/billing/view', 'id' => $bill->bill_id]) : '#' ?>" class="block block-rounded block-link-shadow h-100 text-center d-flex align-items-center justify-content-center p-3">
                <div>
                    <div class="fs-xs fw-bold text-uppercase text-muted mb-1">Billing Status</div>
                    <div class="fs-3 fw-bold <?= $billColor ?>"><i class="fa fa-file-invoice-dollar"></i></div>
                    <div class="fs-sm fw-bold <?= $billColor ?> mt-1"><?= $billStatus ?></div>
                </div>
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (!$model->is_truck_only): ?>
    <div class="block block-rounded content-card mb-4">
        <div class="block-header block-header-default">
            <h3 class="block-title fs-sm text-uppercase fw-bold"><i class="fa fa-info-circle me-1"></i> Specifications</h3>
        </div>
        <div class="block-content">
            <div class="row g-4 pb-4">
                <div class="col-md-3 col-6 border-end">
                    <div class="fs-xs text-muted text-uppercase">Type / Size</div>
                    <div class="fw-bold fs-5">
                        <?= $model->containerType ? $model->containerType->size . "' " . $model->containerType->type_group : '-' ?>
                    </div>
                    <div class="fs-xs text-primary"><?= $model->containerType->iso_code ?? '' ?></div>
                </div>
                <div class="col-md-3 col-6 border-end">
                    <div class="fs-xs text-muted text-uppercase">Shipping Line</div>
                    <div class="fw-bold fs-5">
                        <?= $model->shippingLine->line_code ?? 'Unknown' ?>
                    </div>
                    <div class="fs-xs"><?= $model->shipping_agent_name ?></div>
                </div>
                <div class="col-md-2 col-4">
                    <div class="fs-xs text-muted text-uppercase">Max Gross</div>
                    <div class="fw-bold"><?= number_format((float)$model->gross_weight) ?> <small>kg</small></div>
                </div>
                <div class="col-md-2 col-4">
                    <div class="fs-xs text-muted text-uppercase">Tare</div>
                    <div class="fw-bold"><?= number_format((float)$model->tare_weight) ?> <small>kg</small></div>
                </div>
                <div class="col-md-2 col-4">
                    <div class="fs-xs text-muted text-uppercase">Payload</div>
                    <div class="fw-bold"><?= number_format((float)$model->payload) ?> <small>kg</small></div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="block block-rounded h-100 border-start border-3 border-success shadow-sm">
            <div class="block-header bg-success-light">
                <h3 class="block-title text-success-dark fw-bold"><i class="fa fa-truck-loading me-2"></i> GATE IN</h3>
                <div class="block-options text-end">
                    <?= $formatDateTime($model->date_in, $model->time_in) ?>
                </div>
            </div>
            <div class="block-content fs-sm">
                <table class="table table-borderless table-striped table-vcenter mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted w-25">Delivered By</td>
                            <td class="fw-bold"><?= $model->containerOwner->owner_name ?? $model->truck_owner_name_in ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Truck / Trailer</td>
                            <td class="fw-bold">
                                <?= $model->vehicle_reg_no_in ?>
                                <?php if ($model->trailer_reg_no_in): ?> / <?= $model->trailer_reg_no_in ?> <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Driver</td>
                            <td><?= $model->driver_name_in ?> <span class="text-muted">(<?= $model->driver_id_in ?>)</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Seal No</td>
                            <td class="fw-bold text-primary"><?= $model->seal_number_in ?></td>
                        </tr>
                        <?php if (!empty($model->comments_in)): ?>
                            <tr>
                                <td class="text-muted">Notes</td>
                                <td class="text-danger fw-bold bg-danger-light p-2 rounded">
                                    <i class="fa fa-flag me-1"></i> <?= $model->comments_in ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="block block-rounded h-100 border-start border-3 border-secondary shadow-sm">
            <div class="block-header bg-body-light">
                <h3 class="block-title text-dark fw-bold"><i class="fa fa-truck-moving me-2"></i> GATE OUT</h3>
                <div class="block-options text-end">
                    <?php if ($model->status === 'GATE_OUT'): ?>
                        <?= $formatDateTime($model->date_out, $model->time_out) ?>
                    <?php else: ?>
                        <span class="badge bg-secondary">PENDING</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="block-content fs-sm">
                <?php if ($model->status === 'GATE_OUT'): ?>
                    <table class="table table-borderless table-striped table-vcenter mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted w-25">Destination</td>
                                <td class="fw-bold text-primary"><?= $model->destination ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Truck / Trailer</td>
                                <td class="fw-bold">
                                    <?= $model->vehicle_reg_no_out ?>
                                    <?php if ($model->trailer_reg_no_out): ?> / <?= $model->trailer_reg_no_out ?> <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Driver</td>
                                <td><?= $model->driver_name_out ?> <span class="text-muted">(<?= $model->driver_id_out ?>)</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Seal No</td>
                                <td class="fw-bold"><?= $model->seal_number_out ?></td>
                            </tr>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-3 text-muted opacity-50">
                            <i class="fa fa-warehouse fa-3x"></i>
                        </div>
                        <h5 class="fw-bold text-muted mb-1">Still in Storage</h5>
                        <p class="fs-sm text-muted">This container has not been gated out yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<h3 class="content-heading mt-4"><i class="fa fa-camera me-2"></i> Evidence & Media</h3>
<div class="row g-4">
    <div class="col-md-8">
        <div class="block block-rounded h-100">
            <div class="block-content">
                <div class="row g-3">
                    <?php
                    $photos = [
                        ['title' => 'Gate In', 'path' => $model->arrival_photo_path],
                        ['title' => 'Survey',  'path' => $model->containerSurvey->survey_photo_path ?? null],
                        ['title' => 'Gate Out', 'path' => $model->departure_photo_path]
                    ];
                    ?>
                    <?php foreach ($photos as $photo): ?>
                        <div class="col-md-4 text-center">
                            <div class="mb-2 fw-bold text-xs text-uppercase text-muted border-bottom pb-1"><?= $photo['title'] ?></div>
                            <?php if ($photo['path']): ?>
                                <a href="<?= Yii::getAlias('@web') . '/' . $photo['path'] ?>" target="_blank" class="d-block img-link img-link-zoom-in">
                                    <img src="<?= Yii::getAlias('@web') . '/' . $photo['path'] ?>" class="img-fluid rounded shadow-sm border" style="height: 160px; width: 100%; object-fit: cover;">
                                </a>
                            <?php else: ?>
                                <div class="bg-body-light rounded d-flex align-items-center justify-content-center text-muted" style="height: 160px; border: 2px dashed #e1e6e9;">
                                    <div><i class="fa fa-camera-retro fa-2x mb-2 opacity-50"></i><br><span class="fs-xs">No Image</span></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="block block-rounded h-100">
            <div class="block-header block-header-default">
                <h3 class="block-title fs-sm">Documents</h3>
            </div>
            <div class="block-content fs-sm p-0">
                <?php if ($model->getVisitDocuments()->exists()): ?>
                    <ul class="nav-items my-2">
                        <?php foreach ($model->visitDocuments as $doc): ?>
                            <li>
                                <a class="d-flex py-2 px-3 align-items-center justify-content-between hover-bg-light" href="<?= Yii::getAlias('@web') . '/' . $doc->file_path ?>" target="_blank">
                                    <span class="d-flex align-items-center">
                                        <i class="fa fa-file-pdf text-danger me-2 fa-lg"></i>
                                        <span class="fw-semibold text-dark"><?= ucfirst($doc->doc_type ?? 'Doc') ?></span>
                                    </span>
                                    <span class="fs-xs text-muted"><?= Yii::$app->formatter->asDate($doc->uploaded_at) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <small>No attachments found.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>