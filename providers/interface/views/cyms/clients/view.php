<?php

use yii\helpers\Html;
use helpers\grid\GridView;
use yii\helpers\Url;

$this->title = $model->owner_name;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="content-heading border-0 m-0 text-muted fw-light">
        Owner Profile
    </h2>
    <?= Html::a('<i class="fa fa-arrow-left me-1"></i> Back to Owners List', ['index'], [
        'class' => 'btn btn-alt-secondary px-4 fw-bold'
    ]) ?>
</div>

<div class="block block-rounded content-card mb-3">
    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
        
        <div>
            <h3 class="fw-bold mb-1 text-primary"><?= Html::encode($model->owner_name) ?></h3>
            <div class="text-muted fs-sm">
                <span class="me-3"><i class="fa fa-phone me-1"></i> <?= $model->owner_contact ?></span>
                <span><i class="fa fa-envelope me-1"></i> <?= $model->owner_email ?></span>
            </div>
        </div>

        <div class="d-flex align-items-start gap-3">
            
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-alt-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa fa-download me-1"></i> Export
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <?= Html::a(
                        '<i class="fa fa-file-pdf me-2 text-danger"></i> Print / PDF',
                        ['export', 'id' => $model->owner_id, 'type' => 'print'],
                        ['class' => 'dropdown-item', 'target' => '_blank']
                    ) ?>
                    <?= Html::a(
                        '<i class="fa fa-file-excel me-2 text-success"></i> Excel',
                        ['export', 'id' => $model->owner_id, 'type' => 'excel'],
                        ['class' => 'dropdown-item']
                    ) ?>
                </div>
            </div>

            <div class="text-end">
                <div class="mb-2">
                    <button class="btn btn-sm btn-alt-primary me-1">
                        <i class="fa fa-box me-1"></i> Yard: <strong><?= $totalContainers ?></strong>
                    </button>
                    <button class="btn btn-sm btn-alt-danger">
                        <i class="fa fa-money-bill-wave me-1"></i> Due: <strong><?= Yii::$app->formatter->asCurrency($totalDue, 'KES') ?></strong>
                    </button>
                </div>
                <div>
                    <?= Html::a('<i class="fa fa-pen me-1"></i> Edit Profile', ['update', 'id' => $model->owner_id], ['class' => 'btn btn-sm btn-secondary w-100']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="block block-rounded content-card">
    <ul class="nav nav-tabs nav-tabs-block" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="tab-yard" data-bs-toggle="tab" data-bs-target="#content-yard">
                <i class="fa fa-warehouse me-1"></i> In Yard
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link text-danger" id="tab-unpaid" data-bs-toggle="tab" data-bs-target="#content-unpaid">
                <i class="fa fa-exclamation-circle me-1"></i> Unpaid Bills
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tab-history" data-bs-toggle="tab" data-bs-target="#content-history">
                <i class="fa fa-history me-1"></i> History & Credit
            </button>
        </li>
    </ul>

    <div class="block-content tab-content">

        <div class="tab-pane active p-3" id="content-yard">
            <?= GridView::widget([
                'dataProvider' => $dataProviderInYard,
                'summary' => '',
                'columns' => [
                    'container_number',
                    'date_in:date',
                    [
                        'label' => 'Days',
                        'contentOptions' => ['class' => 'fw-bold text-center'],
                        'value' => function ($m) {
                            // 1. Get Timestamp of Entry
                            $start = strtotime($m->date_in . ' ' . ($m->time_in ?: '00:00:00'));
                            
                            // 2. Calculate Difference
                            $diff = time() - $start;
                            
                            // 3. Apply Logic: Floor + 1 (Part of a day = 1 Day)
                            $days = ($diff < 0) ? 1 : (floor($diff / 86400) + 1);
                            
                            return $days . ' days';
                        }
                    ],
                    'status',
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('View', ['/dashboard/visit/view', 'id' => $model->visit_id], ['class' => 'btn btn-sm btn-alt-secondary']);
                            }
                        ]
                    ]
                ],
            ]); ?>
        </div>

        <div class="tab-pane p-3" id="content-unpaid">
            <?= GridView::widget([
                'dataProvider' => $dataProviderUnpaid,
                'summary' => '',
                'emptyText' => 'No unpaid bills found.',
                'columns' => [
                    'invoice_number',
                    'visit.container_number',
                    'storage_days',
                    [
                        'attribute' => 'balance',
                        'format' => ['currency', 'KES'],
                        'contentOptions' => ['class' => 'text-danger fw-bold'],
                    ],
                    [
                        'label' => 'Action',
                        'format' => 'raw',
                        'value' => function ($m) {
                            return Html::a('<i class="fa fa-credit-card me-1"></i> Pay / Credit', ['/dashboard/billing/view', 'id' => $m->bill_id], ['class' => 'btn btn-sm btn-warning']);
                        }
                    ]
                ],
            ]); ?>
        </div>

        <div class="tab-pane p-3" id="content-history">
            <?= GridView::widget([
                'dataProvider' => $dataProviderHistory,
                'summary' => '',
                'columns' => [
                    'invoice_number',
                    'visit.container_number',
                    'updated_at:date:Paid Date',
                    [
                        'attribute' => 'grand_total',
                        'format' => ['currency', 'KES'],
                    ],
                    [
                        'attribute' => 'total_paid',
                        'format' => ['currency', 'KES'],
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($m) {
                            $cls = $m->status == 'CREDIT' ? 'bg-info' : 'bg-success';
                            return "<span class='badge $cls'>{$m->status}</span>";
                        }
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'urlCreator' => function ($action, $model) {
                            return \yii\helpers\Url::to(['/dashboard/billing/view', 'id' => $model->bill_id]);
                        }
                    ]
                ],
            ]); ?>
        </div>

    </div>
</div>