<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model dashboard\models\ContainerVisits */

$this->title = $model->container_number;
$this->params['breadcrumbs'][] = ['label' => 'Gate IN Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="block block-rounded content-card">
    <div class="block-header block-header-default">
        <h3 class="block-title">
            Container Details: <span class="text-primary fw-bold"><?= Html::encode($this->title) ?></span>
        </h3>
        <div class="block-options">
            <!-- Status Badge -->
            <?php 
                $statusClass = match($model->status) {
                    'IN_YARD' => 'bg-warning',
                    'SURVEYED' => 'bg-info',
                    'GATE_OUT' => 'bg-secondary',
                    default => 'bg-primary'
                };
            ?>
            <span class="badge <?= $statusClass ?> fs-sm me-2"><?= $model->status ?></span>
            
            <?= Html::a('<i class="fa fa-arrow-left me-1"></i> Back', ['index'], ['class' => 'btn btn-sm btn-alt-secondary']) ?>
        </div>
    </div>
    
    <div class="block-content block-content-full">
        <div class="row">
            <!-- LEFT COLUMN: Entry Info -->
            <div class="col-md-6">
                <h5 class="border-bottom pb-2 mb-3 text-muted">Ticket & Entry Info</h5>
                <?= DetailView::widget([
                    'model' => $model,
                    'options' => ['class' => 'table table-striped table-bordered detail-view'],
                    'attributes' => [
                        'ticket_no_in',
                        'date_in:date',
                        'time_in',
                        'container_number',
                        'seal_number_in',
                        'shippingLine.line_name:text:Shipping Line', // Relation
                    ],
                ]) ?>
                
                <h5 class="border-bottom pb-2 mb-3 text-muted mt-4">Evidence</h5>
                <?php if ($model->arrival_photo_path): ?>
                    <div class="p-2 border rounded bg-light d-inline-block">
                        <img src="<?= Yii::getAlias('@web') . '/' . $model->arrival_photo_path ?>" 
                             style="max-width: 100%; max-height: 300px;" 
                             alt="Arrival Photo">
                    </div>
                <?php else: ?>
                    <p class="text-muted fs-sm font-italic">No photo evidence uploaded.</p>
                <?php endif; ?>
            </div>

            <!-- RIGHT COLUMN: Truck & Driver -->
            <div class="col-md-6">
                <h5 class="border-bottom pb-2 mb-3 text-muted">Transporter Details</h5>
                <?= DetailView::widget([
                    'model' => $model,
                    'options' => ['class' => 'table table-striped table-bordered detail-view'],
                    'attributes' => [
                        'vehicle_reg_no_in',
                        'trailer_reg_no_in',
                        'truck_type_in',
                        'driver_name_in',
                        'driver_id_in',
                        'truck_owner_name_in',
                        'truck_owner_contact_in',
                    ],
                ]) ?>

                <?php if ($model->status === 'GATE_OUT'): ?>
                    <h5 class="border-bottom pb-2 mb-3 text-danger mt-4">Exit Info</h5>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'ticket_no_out',
                            'date_out:date',
                            'time_out',
                            'destination',
                            'storage_days',
                        ],
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>