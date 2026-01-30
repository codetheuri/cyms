<?php

use helpers\Html;
use yii\helpers\Url;
use helpers\grid\GridView;

/** @var yii\web\View $this */
/** @var dashboard\models\search\BillingRecordsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Billing & Invoices';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="billing-records-index row">
    <div class="col-md-12">
        <div class="block block-rounded content-card">
            <div class="block-header block-header-default">
                <h3 class="block-title fw-bold">
                    <i class="fa fa-file-invoice-dollar me-2 text-muted"></i><?= Html::encode($this->title) ?> 
                </h3>
            </div>
            
            <div class="block-content">     
                
                <div class="billing-records-search my-3 border-bottom pb-3">
                    <?= $this->render('_search', ['model' => $searchModel]); ?>
                </div>

                <div class="table-responsive">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'tableOptions' => ['class' => 'table table-striped table-hover table-vcenter'],
                        'emptyText' => '
                            <div class="text-center py-4">
                                <i class="fa fa-file-invoice fa-3x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No invoices found.</p>
                            </div>
                        ',
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            // 1. Invoice Number
                            [
                                'attribute' => 'invoice_number',
                                'label' => 'Invoice #',
                                'format' => 'raw',
                                'value' => function($model) {
                                    $num = $model->invoice_number ?: 'DRAFT-' . $model->bill_id;
                                    return Html::a($num, ['view', 'id' => $model->bill_id], ['class' => 'fw-bold text-dark', 'data-pjax' => 0]);
                                },
                                'contentOptions' => ['class' => 'fs-sm fw-bold text-nowrap'],
                            ],

                            // 2. Container Link
                            [
                                'attribute' => 'visit_id',
                                'label' => 'Container',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return Html::a(
                                        $model->visit->container_number, 
                                        ['/dashboard/visit/view', 'id' => $model->visit_id], 
                                        ['class' => 'fw-bold text-primary', 'data-pjax' => 0, 'title' => 'View Container Details']
                                    );
                                }
                            ],

                            // 3. Client / Owner
                            [
                                'attribute' => 'owner_name', 
                                'label' => 'Client',
                                'value' => function($model) {
                                    $owner = $model->visit->containerOwner->owner_name ?? null;
                                    if (!$owner) {
                                        $owner = $model->visit->truck_owner_name_in;
                                    }
                                    return \yii\helpers\StringHelper::truncate($owner ?: 'N/A', 20);
                                },
                                'contentOptions' => ['class' => 'fs-sm text-muted'],
                            ],

                            // 4. Storage Days
                            [
                                'attribute' => 'storage_days',
                                'label' => 'Days',
                                'contentOptions' => ['class' => 'text-center fw-bold'],
                                'headerOptions' => ['class' => 'text-center'],
                            ],
                            
                            // 5. ATL (New Column - Shows if Credit)
                            [
                                'attribute' => 'atl_number',
                                'label' => 'ATL',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return $model->atl_number ? '<span class="badge bg-info-light text-info">' . $model->atl_number . '</span>' : '-';
                                },
                                'headerOptions' => ['class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center fs-xs'],
                            ],

                            // 6. Balance
                            [
                                'attribute' => 'balance',
                                'format' => ['decimal', 2],
                                'label' => 'Bal (KES)',
                                'contentOptions' => function ($model) {
                                    return ['class' => $model->balance > 0.01 ? 'text-end text-danger fw-bold text-nowrap' : 'text-end text-success fw-bold text-nowrap'];
                                }
                            ],

                            // 7. Status Badge
                            [
                                'attribute' => 'status',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $color = match($model->status) {
                                        'PAID' => 'success',
                                        'PARTIAL' => 'warning',
                                        'CREDIT' => 'info',
                                        default => 'danger'
                                    };
                                    return "<span class='badge rounded-pill bg-{$color}'>{$model->status}</span>";
                                },
                                'contentOptions' => ['class' => 'text-center'],
                            ],

                            // 8. Actions
                            [
                                'class' => \helpers\grid\ActionColumn::className(),
                                'header' => 'Manage',
                                'template' => '<div class="btn-group btn-group-sm">{view} {pay}</div>',
                                'headerOptions' => ['width' => '100px', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                                'buttons' => [
                                    'view' => function ($url, $model) {
                                        return Html::a('<i class="fa fa-eye"></i>', ['view', 'id' => $model->bill_id], [
                                            'class' => 'btn btn-alt-secondary',
                                            'title' => 'View Invoice',
                                            'data-pjax' => 0
                                        ]);
                                    },
                                    'pay' => function ($url, $model) {
                                        if ($model->balance > 0.01 && $model->status !== 'CREDIT') {
                                            return Html::a('<i class="fa fa-cash-register"></i>', ['view', 'id' => $model->bill_id], [
                                                'class' => 'btn btn-primary',
                                                'title' => 'Record Payment',
                                                'data-pjax' => 0
                                            ]);
                                        }
                                        return '';
                                    },
                                ],
                            ],
                        ],
                    ]); ?>
                </div> 
            </div>
        </div>
    </div>
</div>