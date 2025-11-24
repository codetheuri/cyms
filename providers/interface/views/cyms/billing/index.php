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
            
          

            <div class="block-content">     
                
                <!-- Search -->
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
                                'value' => function($model) {
                                    return $model->invoice_number ?: 'DRAFT-' . $model->bill_id;
                                },
                                'contentOptions' => ['class' => 'fs-sm text-muted fw-bold text-nowrap'],
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

                            // 3. Client / Owner (FIXED)
                            [
                                'attribute' => 'owner_name', 
                                'label' => 'Client',
                                'value' => function($model) {
                                    // Logic: Check Relation -> Check Text Field -> Return Fallback
                                    $owner = $model->visit->containerOwner->owner_name ?? null;
                                    if (!$owner) {
                                        $owner = $model->visit->truck_owner_name_in;
                                    }
                                    return $owner ?: 'N/A';
                                }
                            ],

                            // 4. Storage Days
                            [
                                'attribute' => 'storage_days',
                                'label' => 'Days',
                                'headerOptions' => ['class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],

                            // 5. Balance
                            [
                                'attribute' => 'balance',
                                'format' => ['currency', 'KES'],
                                'contentOptions' => function ($model) {
                                    return ['class' => $model->balance > 0.01 ? 'text-end text-danger fw-bold text-nowrap' : 'text-end text-success fw-bold text-nowrap'];
                                }
                            ],

                            // 6. Status Badge
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

                            // 7. Actions (FIXED)
                            [
                                'class' => \helpers\grid\ActionColumn::className(),
                                'header' => 'Manage',
                                'template' => '{view} {pay}', // Template defines order
                                'headerOptions' => ['width' => '140px', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                                'buttons' => [
                                    // Button A: VIEW (Always Visible)
                                    'view' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fa fa-eye"></i>',
                                            ['view', 'id' => $model->bill_id],
                                            ['class' => 'btn btn-sm btn-alt-secondary me-1', 'title' => 'View Invoice', 'data-pjax' => 0]
                                        );
                                    },

                                    // Button B: PAY (Conditional)
                                    'pay' => function ($url, $model, $key) {
                                        // Show only if Balance > 0 AND not authorized credit
                                        if ($model->balance > 0.01 ) {
                                            return Html::a(
                                                '<i class="fa fa-money-bill-wave"></i>',
                                                ['view', 'id' => $model->bill_id], // Redirects to same page, but intent is clear
                                                ['class' => 'btn btn-sm btn-primary', 'title' => 'Record Payment', 'data-pjax' => 0]
                                            );
                                        }
                                        return ''; // Return nothing if paid
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