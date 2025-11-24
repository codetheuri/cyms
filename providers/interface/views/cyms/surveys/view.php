<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model dashboard\models\ContainerSurveys */

$this->title = 'Survey Details: ' . $model->visit->container_number;
$this->params['breadcrumbs'][] = ['label' => 'Survey History', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="block block-rounded">
    <div class="block-header block-header-default">
        <h3 class="block-title"><?= Html::encode($this->title) ?></h3>
        <div class="block-options">
            <?= Html::a('<i class="fa fa-arrow-left me-1"></i> Back', ['index'], ['class' => 'btn btn-sm btn-alt-secondary']) ?>
            
            <?= Html::a('<i class="fa fa-print me-1"></i> Print Report', ['/dashboard/reports/inward', 'id' => $model->visit_id], [
                'class' => 'btn btn-sm btn-alt-info',
                'target' => '_blank',
                'data-pjax' => 0,
            ]) ?>
        </div>
    </div>
    
    <div class="block-content">
        
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="border-bottom pb-2 text-muted">General Info</h5>
                <?= DetailView::widget([
                    'model' => $model,
                    'options' => ['class' => 'table table-striped table-bordered detail-view'],
                    'attributes' => [
                        'survey_id',
                        [
                            'label' => 'Container Number',
                            'value' => $model->visit->container_number,
                            'contentOptions' => ['class' => 'fw-bold'],
                        ],
                        'survey_date:datetime',
                        'surveyor_name',
                        [
                            'attribute' => 'approval_status',
                            'format' => 'raw',
                            'value' => function($model) {
                                $badge = $model->approval_status == 'APPROVED' ? 'bg-success' : 'bg-warning';
                                return "<span class='badge $badge'>{$model->approval_status}</span>";
                            }
                        ],
                    ],
                ]) ?>
            </div>
            <div class="col-md-6">
                <h5 class="border-bottom pb-2 text-muted">Visit Context</h5>
                <?= DetailView::widget([
                    'model' => $model->visit,
                    'options' => ['class' => 'table table-striped table-bordered detail-view'],
                    'attributes' => [
                        'ticket_no_in',
                        'date_in:date',
                        'truck_owner_name_in:text:Owner',
                        'driver_name_in',
                    ],
                ]) ?>
            </div>
        </div>

        <h5 class="border-bottom pb-2 mb-3 text-danger">
            <i class="fa fa-exclamation-triangle me-1"></i> Identified Damages
        </h5>

        <div class="table-responsive mb-4">
            <table class="table table-bordered table-vcenter">
                <thead class="bg-body-light">
                    <tr>
                        <th style="width: 15%;">Code</th>
                        <th>Description</th>
                        <th class="text-center" style="width: 10%;">Qty</th>
                        <th class="text-end" style="width: 15%;">Labor</th>
                        <th class="text-end" style="width: 15%;">Material</th>
                        <th class="text-end" style="width: 15%;">Total Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grandTotal = 0;
                    foreach ($model->surveyDamages as $damage): 
                        $grandTotal += $damage->total_cost;
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= Html::encode($damage->repair_code) ?></td>
                        <td><?= Html::encode($damage->description) ?></td>
                        <td class="text-center"><?= $damage->quantity ?></td>
                        <td class="text-end"><?= Yii::$app->formatter->asCurrency($damage->labor_cost) ?></td>
                        <td class="text-end"><?= Yii::$app->formatter->asCurrency($damage->material_cost) ?></td>
                        <td class="text-end fw-bold"><?= Yii::$app->formatter->asCurrency($damage->total_cost) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($model->surveyDamages)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-3">No damages recorded. Container is Sound.</td></tr>
                    <?php endif; ?>
                </tbody>
                <?php if ($grandTotal > 0): ?>
                <tfoot>
                    <tr class="table-active">
                        <td colspan="5" class="text-end fw-bold text-uppercase">Total Repair Cost</td>
                        <td class="text-end fw-bold text-success fs-5">
                            <?= Yii::$app->formatter->asCurrency($grandTotal) ?>
                        </td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>

    </div>
</div>