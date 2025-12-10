
<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model dashboard\models\ContainerVisits */

$this->title = $model->container_number;
$this->params['breadcrumbs'][] = ['label' => 'Gate Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Helper for Status Badge
$statusClass = match($model->status) {
    'IN_YARD' => 'bg-warning',
    'SURVEYED' => 'bg-info',
    'GATE_OUT' => 'bg-secondary',
    default => 'bg-primary'
};
?>

<div class="block block-rounded content-card mb-3">
    <div class="block-content block-content-full d-flex justify-content-between align-items-center">
        <div>
            <h3 class="block-title fs-2 fw-bold text-primary mb-0">
                <i class="fa fa-box me-2"></i><?= Html::encode($model->container_number) ?>
            </h3>
            <span class="badge <?= $statusClass ?> fs-sm"><?= $model->status ?></span>
            <span class="text-muted fs-sm ms-2">Ticket: <strong><?= $model->ticket_no_in ?></strong></span>
        </div>
        <div>
            <?= Html::a('<i class="fa fa-arrow-left me-1"></i> Back', ['index'], ['class' => 'btn btn-sm btn-alt-secondary']) ?>
            
            <?php if ($model->status === 'SURVEYED' || $model->status === 'GATE_OUT'): ?>
                <a href="<?= Url::to(['/dashboard/reports/inward', 'id' => $model->visit_id]) ?>" target="_blank" class="btn btn-sm btn-success ms-1">
                    <i class="fa fa-print me-1"></i> Inward Rpt
                </a>
            <?php endif; ?>
            
            <?php if ($model->status === 'GATE_OUT'): ?>
                <a href="<?= Url::to(['/dashboard/reports/outward', 'id' => $model->visit_id]) ?>" target="_blank" class="btn btn-sm btn-danger ms-1">
                    <i class="fa fa-print me-1"></i> Outward Rpt
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="block block-rounded content-card mb-3">
    <div class="block-header block-header-default">
        <h3 class="block-title fs-sm text-uppercase fw-bold"><i class="fa fa-info-circle me-1"></i> Container Specifications</h3>
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
                <div class="fw-bold"><?= number_format($model->gross_weight) ?> <small>kg</small></div>
            </div>
            <div class="col-md-2 col-4">
                <div class="fs-xs text-muted text-uppercase">Tare</div>
                <div class="fw-bold"><?= number_format($model->tare_weight) ?> <small>kg</small></div>
            </div>
            <div class="col-md-2 col-4">
                <div class="fs-xs text-muted text-uppercase">Payload</div>
                <div class="fw-bold"><?= number_format($model->payload) ?> <small>kg</small></div>
            </div>
        </div>
        <div class="row g-4 pb-3 border-top pt-3">
             <div class="col-md-3">
                <div class="fs-xs text-muted text-uppercase">Manufacture Date</div>
                <div class="fw-bold"><?= $model->date_of_manufacture ?? '-' ?></div>
            </div>
             <div class="col-md-3">
                <div class="fs-xs text-muted text-uppercase">Vessel / Voyage</div>
                <div class="fw-bold"><?= $model->vessel_name ?? '-' ?> / <?= $model->voyage_number ?? '-' ?></div>
            </div>
             <div class="col-md-3">
                <div class="fs-xs text-muted text-uppercase">BL Number</div>
                <div class="fw-bold"><?= $model->bl_number ?? '-' ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="block block-rounded content-card h-100 border-start border-3 border-success">
            <div class="block-header bg-body-light">
                <h3 class="block-title text-success"><i class="fa fa-truck-loading me-2"></i> Gate IN Details</h3>
                <div class="block-options fs-sm fw-bold">
                    <?= Yii::$app->formatter->asDate($model->date_in) ?> | <?= $model->time_in ?>
                </div>
            </div>
            <div class="block-content">
                <?= DetailView::widget([
                    'model' => $model,
                    'options' => ['class' => 'table table-vcenter table-borderless fs-sm'],
                    'attributes' => [
                        [
                            'label' => 'Client / Owner',
                            'value' => $model->containerOwner->owner_name ?? $model->truck_owner_name_in,
                            'contentOptions' => ['class' => 'fw-bold'],
                        ],
                        'vehicle_reg_no_in',
                        'trailer_reg_no_in',
                        'driver_name_in',
                        'driver_id_in',
                        'seal_number_in',
                        'comments_in:ntext',
                    ],
                ]) ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="block block-rounded content-card h-100 border-start border-3 border-danger">
            <div class="block-header bg-body-light">
                <h3 class="block-title text-danger"><i class="fa fa-truck-moving me-2"></i> Gate OUT Details</h3>
                <?php if ($model->status === 'GATE_OUT'): ?>
                    <div class="block-options fs-sm fw-bold">
                        <?= Yii::$app->formatter->asDate($model->date_out) ?> | <?= $model->time_out ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="block-content">
                <?php if ($model->status === 'GATE_OUT'): ?>
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-vcenter table-borderless fs-sm'],
                        'attributes' => [
                            [
                                'label' => 'Destination',
                                'value' => $model->destination,
                                'contentOptions' => ['class' => 'fw-bold'],
                            ],
                            'vehicle_reg_no_out',
                            'trailer_reg_no_out',
                            'driver_name_out',
                            'driver_id_out',
                            'seal_number_out',
                            [
                                'label' => 'Total Stay',
                                'value' => $model->storage_days . ' Days',
                                'contentOptions' => ['class' => 'text-danger fw-bold'],
                            ]
                        ],
                    ]) ?>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa fa-clock fa-2x mb-2"></i><br>
                        Container is currently in yard.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<h3 class="content-heading mt-4"><i class="fa fa-images me-2"></i> Media & Documentation</h3>
<div class="row">
    <div class="col-md-8">
        <div class="block block-rounded content-card h-100">
            <div class="block-header block-header-default">
                <h3 class="block-title">Container Photos</h3>
            </div>
            <div class="block-content">
                <div class="row g-3">
                    <div class="col-md-4 text-center">
                        <div class="mb-2 fw-bold text-xs text-uppercase text-muted">Gate In</div>
                        <?php if ($model->arrival_photo_path): ?>
                            <a href="<?= Yii::getAlias('@web') . '/' . $model->arrival_photo_path ?>" target="_blank">
                                <img src="<?= Yii::getAlias('@web') . '/' . $model->arrival_photo_path ?>" class="img-fluid rounded shadow-sm border" style="height: 150px; object-fit: cover;">
                            </a>
                        <?php else: ?>
                            <div class="bg-body-light rounded d-flex align-items-center justify-content-center" style="height: 150px; border: 2px dashed #ddd;">
                                <span class="text-muted fs-xs">No Photo</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4 text-center">
                        <div class="mb-2 fw-bold text-xs text-uppercase text-muted">Survey</div>
                        <?php 
                        $surveyPhoto = $model->containerSurvey->survey_photo_path ?? null;
                        if ($surveyPhoto): ?>
                            <a href="<?= Yii::getAlias('@web') . '/' . $surveyPhoto ?>" target="_blank">
                                <img src="<?= Yii::getAlias('@web') . '/' . $surveyPhoto ?>" class="img-fluid rounded shadow-sm border" style="height: 150px; object-fit: cover;">
                            </a>
                        <?php else: ?>
                            <div class="bg-body-light rounded d-flex align-items-center justify-content-center" style="height: 150px; border: 2px dashed #ddd;">
                                <span class="text-muted fs-xs">No Photo</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4 text-center">
                        <div class="mb-2 fw-bold text-xs text-uppercase text-muted">Gate Out</div>
                        <?php if ($model->departure_photo_path): ?>
                            <a href="<?= Yii::getAlias('@web') . '/' . $model->departure_photo_path ?>" target="_blank">
                                <img src="<?= Yii::getAlias('@web') . '/' . $model->departure_photo_path ?>" class="img-fluid rounded shadow-sm border" style="height: 150px; object-fit: cover;">
                            </a>
                        <?php else: ?>
                            <div class="bg-body-light rounded d-flex align-items-center justify-content-center" style="height: 150px; border: 2px dashed #ddd;">
                                <span class="text-muted fs-xs">No Photo</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="block block-rounded content-card h-100">
            <div class="block-header block-header-default">
                <h3 class="block-title">Attached Documents</h3>
            </div>
            <div class="block-content fs-sm">
                <?php if ($model->getVisitDocuments()->exists()): ?>
                    <ul class="list-group list-group-flush push">
                        <?php foreach ($model->visitDocuments as $doc): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                            <div>
                                <i class="fa fa-file-pdf text-danger me-2"></i>
                                <span class="fw-semibold"><?= ucfirst($doc->doc_type ?? 'Document') ?></span>
                                <div class="fs-xs text-muted"><?= Yii::$app->formatter->asDate($doc->uploaded_at) ?></div>
                            </div>
                            <a href="<?= Yii::getAlias('@web') . '/' . $doc->file_path ?>" target="_blank" class="btn btn-sm btn-alt-secondary">
                                <i class="fa fa-download"></i>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fa fa-folder-open fa-2x mb-2"></i><br>
                        No documents attached.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
