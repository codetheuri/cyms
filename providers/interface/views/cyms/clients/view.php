<?php

use yii\helpers\Html;
use helpers\grid\GridView;
use yii\helpers\Url;
use yii\widgets\ActiveForm; // Required for the Modal Form

/* @var $this yii\web\View */
/* @var $model dashboard\models\ContainerOwners */

$this->title = $model->owner_name;
?>

<div class="bg-body-light border-bottom mb-4">
    <div class="content py-3">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
            <h1 class="flex-grow-1 fs-3 fw-bold my-2 my-sm-3"><?= Html::encode($model->owner_name) ?></h1>
            <nav class="flex-shrink-0 my-2 my-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">Clients</li>
                    <li class="breadcrumb-item active" aria-current="page">Profile</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="block block-rounded shadow-sm mb-4">
    <div class="block-content block-content-full bg-primary-dark rounded-top text-white">
        <div class="d-flex justify-content-between align-items-center p-2">
            <div>
                <h2 class="text-white fw-bold mb-1"><?= Html::encode($model->owner_name) ?></h2>
                <div class="text-white-75 fs-sm">
                    <span class="me-3"><i class="fa fa-phone me-1"></i> <?= $model->owner_contact ?></span>
                    <span><i class="fa fa-envelope me-1"></i> <?= $model->owner_email ?></span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <?= Html::a('<i class="fa fa-pen me-1"></i> Edit Details', ['update', 'id' => $model->owner_id], ['class' => 'btn btn-sm btn-light fw-bold']) ?>
                <div class="dropdown">
                    <button type="button" class="btn btn-sm btn-alt-secondary bg-white dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-download"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <?= Html::a('<i class="fa fa-file-pdf me-2 text-danger"></i> Print Profile', ['export', 'id' => $model->owner_id, 'type' => 'print'], ['class' => 'dropdown-item', 'target' => '_blank']) ?>
                        <?= Html::a('<i class="fa fa-file-excel me-2 text-success"></i> Export Excel', ['export', 'id' => $model->owner_id, 'type' => 'excel'], ['class' => 'dropdown-item']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="block-content block-content-full d-flex align-items-center justify-content-around bg-body-light">
        <div class="text-center">
            <div class="fs-1 fw-bold text-primary"><?= $totalContainers ?></div>
            <div class="fs-sm fw-medium text-uppercase text-muted">Active Units</div>
        </div>
        <div class="text-center border-start border-end px-5">
            <div class="fs-1 fw-bold text-danger"><?= Yii::$app->formatter->asCurrency($totalDue, 'KES') ?></div>
            <div class="fs-sm fw-medium text-uppercase text-muted">Outstanding Balance</div>
        </div>
        <div class="text-center">
            <div class="fs-1 fw-bold text-success"><?= $model->getBillingRecords()->where(['status' => 'PAID'])->count() ?></div>
            <div class="fs-sm fw-medium text-uppercase text-muted">Paid Invoices</div>
        </div>
    </div>
</div>

<div class="block block-rounded shadow-sm">
    <div class="block-header block-header-default p-0">
        <ul class="nav nav-tabs nav-tabs-block nav-justified" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="tab-yard" data-bs-toggle="tab" data-bs-target="#content-yard">
                    <i class="fa fa-warehouse me-2 text-muted"></i> In Yard
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-unpaid" data-bs-toggle="tab" data-bs-target="#content-unpaid">
                    <i class="fa fa-file-invoice-dollar me-2 text-danger"></i> Unpaid Bills
                    <?php if ($totalDue > 0): ?>
                        <span class="badge rounded-pill bg-danger ms-1">!</span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-rates" data-bs-toggle="tab" data-bs-target="#content-rates">
                    <i class="fa fa-tags me-2 text-warning"></i> Custom Rates
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-history" data-bs-toggle="tab" data-bs-target="#content-history">
                    <i class="fa fa-history me-2 text-muted"></i> History
                </button>
            </li>
        </ul>
    </div>

    <div class="block-content tab-content overflow-hidden">

        <div class="tab-pane fade show active p-4" id="content-yard" role="tabpanel">
            <?= GridView::widget([
                'dataProvider' => $dataProviderInYard,
                'summary' => '',
                'tableOptions' => ['class' => 'table table-striped table-vcenter table-hover'],
                'columns' => [
                    [
                        'attribute' => 'container_number',
                        'format' => 'raw',
                        'value' => function ($m) {
                            return '<span class="fw-bold text-primary">' . $m->container_number . '</span>';
                        }
                    ],
                    'date_in:date',
                    [
                        'label' => 'Duration',
                        'contentOptions' => ['class' => 'text-center'],
                        'value' => function ($m) {
                            $start = strtotime($m->date_in . ' ' . ($m->time_in ?: '00:00:00'));
                            $diff = time() - $start;
                            $days = ($diff < 0) ? 1 : (floor($diff / 86400) + 1);
                            return $days . ' Days';
                        }
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($m) {
                            $color = $m->status == 'IN_YARD' ? 'info' : 'warning';
                            return "<span class='badge bg-$color'>{$m->status}</span>";
                        }
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('<i class="fa fa-eye"></i> View', ['/dashboard/visit/view', 'id' => $model->visit_id], ['class' => 'btn btn-sm btn-light']);
                            }
                        ]
                    ]
                ],
            ]); ?>
        </div>

        <div class="tab-pane fade p-4" id="content-unpaid" role="tabpanel">
            <?= GridView::widget([
                'dataProvider' => $dataProviderUnpaid,
                'summary' => '',
                'emptyText' => '<div class="text-center text-muted py-4"><i class="fa fa-check-circle fa-3x text-success mb-2"></i><br>No outstanding bills.</div>',
                'tableOptions' => ['class' => 'table table-vcenter table-hover'],
                'columns' => [
                    'invoice_number',
                    'visit.container_number',
                    'storage_days',
                    [
                        'attribute' => 'balance',
                        'format' => ['currency', 'KES'],
                        'contentOptions' => ['class' => 'text-danger fw-bold text-end'],
                    ],
                    [
                        'label' => 'Action',
                        'format' => 'raw',
                        'contentOptions' => ['class' => 'text-center'],
                        'value' => function ($m) use ($model) { // Note: pass $model to use owner_id
                            return Html::a(
                                'Pay Now',
                                [
                                    '/dashboard/billing/view',
                                    'id' => $m->bill_id,
                                    'return_client' => $model->owner_id // <--- NEW PARAMETER
                                ],
                                ['class' => 'btn btn-sm btn-primary px-3']
                            );
                        }
                    ]
                ],
            ]); ?>
        </div>

        <div class="tab-pane fade p-4" id="content-rates" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3 bg-body-light p-3 rounded">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">Negotiated Rates</h4>
                    <p class="mb-0 fs-sm text-muted">Set specific daily rates for container types for this client.</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-rate">
                    <i class="fa fa-plus-circle me-1"></i> Add New Rate
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-vcenter">
                    <thead class="bg-body-dark text-white">
                        <tr>
                            <th>Container Type</th>
                            <th>Global Rate (Default)</th>
                            <th>Client Rate (Special)</th>
                            <th class="text-center" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rates = \dashboard\models\ClientRates::find()->where(['owner_id' => $model->owner_id])->all();
                        if ($rates):
                            foreach ($rates as $rate):
                        ?>
                                <tr>
                                    <td class="fw-bold">
                                        <i class="fa fa-box me-2 text-muted"></i>
                                        <?= $rate->containerType->size . "' " . $rate->containerType->type_group ?>
                                        <span class="badge bg-secondary ms-1"><?= $rate->containerType->iso_code ?></span>
                                    </td>
                                    <td class="text-muted">
                                        <?= number_format($rate->containerType->daily_rate ?? Yii::$app->config->get('storage_rate_per_day'), 2) ?>
                                    </td>
                                    <td class="fw-bold text-success fs-5">
                                        <?= number_format($rate->daily_rate, 2) ?>
                                    </td>
                                    <td class="text-center">
                                        <?= Html::a('<i class="fa fa-trash-alt"></i>', ['delete-rate', 'id' => $rate->rate_id], [
                                            'class' => 'btn btn-sm btn-outline-danger',
                                            'data-method' => 'post',
                                            'data-confirm' => 'Are you sure you want to remove this custom rate? Standard rates will apply.',
                                            'title' => 'Remove Rate'
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No custom rates defined. Standard rates apply.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade p-4" id="content-history" role="tabpanel">
            <?= GridView::widget([
                'dataProvider' => $dataProviderHistory,
                'summary' => '',
                'tableOptions' => ['class' => 'table table-striped table-vcenter'],
                'columns' => [
                    'invoice_number',
                    'visit.container_number',
                    'updated_at:date:Paid/Auth Date',
                    [
                        'attribute' => 'grand_total',
                        'format' => ['currency', 'KES'],
                        'contentOptions' => ['class' => 'text-end'],
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
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('<i class="fa fa-file-alt"></i> View', ['/dashboard/billing/view', 'id' => $model->bill_id], ['class' => 'btn btn-sm btn-light']);
                            }
                        ]
                    ]
                ],
            ]); ?>
        </div>

    </div>
</div>

<div class="modal fade" id="modal-add-rate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <?php $rateModel = new \dashboard\models\ClientRates(['owner_id' => $model->owner_id]); ?>
            <?php $form = ActiveForm::begin(['action' => ['add-rate', 'id' => $model->owner_id]]); ?>

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fa fa-tag me-1"></i> Define Custom Rate</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-info fs-sm mb-3">
                    <i class="fa fa-info-circle me-1"></i> Select a container type and set the negotiated daily storage rate. This will override the global default.
                </div>

                <div class="mb-3">
                    <?= $form->field($rateModel, 'container_type_id')->dropDownList(
                        \yii\helpers\ArrayHelper::map(\dashboard\models\MasterContainerTypes::find()->all(), 'type_id', function ($t) {
                            return $t->size . "' " . $t->type_group . ' (' . $t->iso_code . ')';
                        }),
                        ['class' => 'form-select form-select-lg']
                    )->label('Container Type') ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Negotiated Daily Rate</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text fw-bold">KES</span>
                        <?= $form->field($rateModel, 'daily_rate', ['options' => ['tag' => false]])->textInput([
                            'type' => 'number',
                            'step' => '0.01',
                            'class' => 'form-control',
                            'placeholder' => '0.00'
                        ])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold px-4">Save Custom Rate</button>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>