<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model dashboard\models\ContainerSurveys */

$this->title = 'Survey #' . $model->survey_id;
$visit = $model->visit;

// Helper: Status Color
$statusColor = match($model->approval_status) {
    'APPROVED' => 'success',
    'REJECTED' => 'danger',
    default => 'warning'
};

// Helper: Owner Logic
$ownerName = $visit->containerOwner->owner_name ?? $visit->truck_owner_name_in ?? '<span class="text-muted">Not Specified</span>';

// Helper: Date & Time
$dateIn = Yii::$app->formatter->asDate($visit->date_in, 'php:d M Y');
$timeIn = $visit->time_in ? date('H:i', strtotime($visit->time_in)) . ' hrs' : '';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="content-heading pt-0 mb-0 border-0">
            <span class="fw-light text-muted">Survey:</span> 
            <span class="fw-bold text-primary"><?= $visit->container_number ?></span>
        </h2>
        <div class="fs-sm text-muted">
            Ticket: <span class="fw-semibold"><?= $visit->ticket_no_in ?></span> &bull; 
            Line: <span class="fw-semibold"><?= $visit->shippingLine->line_code ?? '-' ?></span>
        </div>
    </div>
    <div>
        <?= Html::a('<i class="fa fa-arrow-left me-1"></i> Back', ['index'], ['class' => 'btn btn-sm btn-alt-secondary']) ?>
        <?= Html::a('<i class="fa fa-print me-1"></i> Print Report', ['/dashboard/reports/inward', 'id' => $model->visit_id], [
            'class' => 'btn btn-sm btn-primary ms-1',
            'target' => '_blank',
            'data-pjax' => 0,
        ]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="block block-rounded text-center d-flex align-items-center justify-content-center p-4 border-start border-5 border-<?= $statusColor ?> mb-3">
            <div>
                <div class="fs-xs fw-bold text-uppercase text-muted mb-1">Survey Status</div>
                <span class="badge bg-<?= $statusColor ?> fs-sm px-3 py-2 rounded-pill"><?= $model->approval_status ?></span>
                <div class="fs-sm text-muted mt-2">
                    <i class="fa fa-user-circle me-1"></i> <?= $model->surveyor_name ?>
                </div>
                <div class="fs-xs text-muted">
                    <?= Yii::$app->formatter->asDate($model->survey_date) ?>
                </div>
            </div>
        </div>

        <div class="block block-rounded content-card">
            <div class="block-header block-header-default">
                <h3 class="block-title fs-sm fw-bold text-uppercase">Visit Context</h3>
            </div>
            <div class="block-content fs-sm">
                <table class="table table-borderless table-striped table-vcenter mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted"><i class="fa fa-user me-2"></i> Owner</td>
                            <td class="fw-bold text-end"><?= $ownerName ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="fa fa-calendar me-2"></i> Date In</td>
                            <td class="fw-bold text-end"><?= $dateIn ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="fa fa-clock me-2"></i> Time In</td>
                            <td class="fw-bold text-end"><?= $timeIn ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="fa fa-truck me-2"></i> Truck</td>
                            <td class="fw-bold text-end"><?= $visit->vehicle_reg_no_in ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="fa fa-id-card me-2"></i> Driver</td>
                            <td class="fw-bold text-end"><?= $visit->driver_name_in ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="block block-rounded content-card mt-3">
            <div class="block-header block-header-default">
                <h3 class="block-title fs-sm fw-bold text-uppercase">Survey Photo</h3>
            </div>
            <div class="block-content text-center p-3">
                <?php if ($model->survey_photo_path): ?>
                    <a href="<?= Yii::getAlias('@web') . '/' . $model->survey_photo_path ?>" target="_blank" class="img-link img-link-zoom-in">
                        <img src="<?= Yii::getAlias('@web') . '/' . $model->survey_photo_path ?>" class="img-fluid rounded shadow-sm border" style="max-height: 200px; width: 100%; object-fit: cover;">
                    </a>
                <?php else: ?>
                    <div class="bg-body-light rounded d-flex align-items-center justify-content-center text-muted" style="height: 150px; border: 2px dashed #e1e6e9;">
                        <div><i class="fa fa-camera fa-2x mb-2 opacity-50"></i><br><small>No Photo Uploaded</small></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="block block-rounded content-card h-100">
            <div class="block-header block-header-default bg-body-light">
                <h3 class="block-title fw-bold text-danger">
                    <i class="fa fa-exclamation-triangle me-2"></i> Damage Assessment
                </h3>
                <div class="block-options">
                    <span class="badge bg-secondary">
                        <?= count($model->surveyDamages) ?> Record(s)
                    </span>
                </div>
            </div>
            
            <div class="block-content p-0">
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover table-bordered border-top-0 mb-0">
                        <thead class="bg-light text-uppercase fs-xs">
                            <tr>
                                <th style="width: 10%;">Code</th>
                                <th>Description</th>
                                <th class="text-center" style="width: 8%;">Qty</th>
                                <th class="text-end" style="width: 15%;">Labor</th>
                                <th class="text-end" style="width: 15%;">Material</th>
                                <th class="text-end" style="width: 15%;">Total</th>
                            </tr>
                        </thead>
                        <tbody class="fs-sm">
                            <?php 
                            $grandTotal = 0;
                            foreach ($model->surveyDamages as $damage): 
                                $grandTotal += $damage->total_cost;
                            ?>
                            <tr>
                                <td class="fw-bold text-primary font-monospace"><?= Html::encode($damage->repair_code) ?></td>
                                <td><?= Html::encode($damage->description) ?></td>
                                <td class="text-center fw-bold"><?= $damage->quantity ?></td>
                                <td class="text-end text-muted"><?= Yii::$app->formatter->asDecimal($damage->labor_cost, 2) ?></td>
                                <td class="text-end text-muted"><?= Yii::$app->formatter->asDecimal($damage->material_cost, 2) ?></td>
                                <td class="text-end fw-bold text-dark"><?= Yii::$app->formatter->asDecimal($damage->total_cost, 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($model->surveyDamages)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-success mb-2"><i class="fa fa-check-circle fa-3x"></i></div>
                                        <h5 class="fw-bold text-muted mb-0">Container is Sound</h5>
                                        <p class="fs-sm text-muted">No damages recorded during this survey.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if ($grandTotal > 0): ?>
                        <tfoot class="bg-body-light border-top">
                            <tr>
                                <td colspan="5" class="text-end fw-bold text-uppercase py-3">Total Estimated Repair Cost</td>
                                <td class="text-end fw-bold text-success fs-5 py-3 pe-3">
                                    <?= Yii::$app->formatter->asCurrency($grandTotal) ?>
                                </td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>