<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var yii\web\View $this */
/* @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Gate IN Records';
?>

<div class="block block-rounded content-card">
    <div class="block-header block-header-default">
        <h3 class="block-title fw-bold"><?= Html::encode($this->title) ?></h3>
        <div class="block-options">
            <?= Html::a('<i class="fa fa-plus me-1"></i> New Entry', ['gate-in'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <div class="block-content block-content-full">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-striped table-hover table-vcenter'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'ticket_no_in',
                [
                    'attribute' => 'container_number',
                    'contentOptions' => ['class' => 'fw-bold'],
                ],
                [
                    'label' => 'Container Type',
                    'attribute' => 'container_type_id',
                    'value' => function($model) {
                        return $model->containerType ? $model->containerType->iso_code : '-';
                    }
                ],
                [
                    'label' => 'Container Line',
                    'attribute' => 'shipping_line_id',
                    'value' => function($model) {
                        return $model->shippingLine ? $model->shippingLine->line_code : '-';
                    }
                ],
                'vehicle_reg_no_in',
                'driver_name_in',
                [
                'label' => 'Owner',
                    'attribute' => 'truck_owner_name_in',
                    'value' => function($model) {
                        
                        return $model->containerOwner ? $model->containerOwner->owner_name:'-';
                    }
                ],
                'date_in:date',
               
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $color = match ($model->status) {
                            'IN_YARD' => 'bg-warning', 
                            'SURVEYED' => 'bg-info',   
                            'GATE_OUT' => 'bg-secondary',
                            default => 'bg-primary'
                        };
                        return "<span class='badge $color'>{$model->status}</span>";
                    }
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => 'Actions',
                    // REMOVED {update} here. Added {view}
                    'template' => '{view} {survey} {print-inward} {print-outward}', 
                    'buttons' => [
                        // --- 1. VIEW BUTTON (Eye Icon) ---
                        'view' => function ($url, $model) {
                            return Html::a(
                                '<i class="fa fa-eye"></i>',
                                ['view', 'id' => $model->visit_id],
                                ['class' => 'btn btn-sm btn-alt-secondary', 'title' => 'View Details']
                            );
                        },

                        // --- 2. SURVEY BUTTON ---
                        'survey' => function ($url, $model) {
                            if ($model->status !== 'GATE_OUT') {
                                $icon = $model->status === 'SURVEYED' ? 'fa-pencil-alt' : 'fa-clipboard-check';
                                $class = $model->status === 'SURVEYED' ? 'btn-alt-secondary' : 'btn-alt-primary';

                                return Html::a(
                                    '<i class="fa ' . $icon . '"></i>',
                                    ['survey', 'visit_id' => $model->visit_id],
                                    ['class' => 'btn btn-sm ' . $class . ' ms-1', 'title' => 'Perform/Edit Survey']
                                );
                            }
                            return '';
                        },

                        // --- 3. PRINT INWARD ---
                        'print-inward' => function ($url, $model) {
                            if ($model->status === 'SURVEYED' || $model->status === 'GATE_OUT') {
                                return Html::a(
                                    '<i class="fa fa-file-import"></i>',
                                    ['/dashboard/reports/inward', 'id' => $model->visit_id],
                                    ['class' => 'btn btn-sm btn-success ms-1', 'title' => 'Print Inward Interchange', 'target' => '_blank']
                                );
                            }
                            return '';
                        },

                        // --- 4. PRINT OUTWARD ---
                        'print-outward' => function ($url, $model) {
                            if ($model->status === 'GATE_OUT') {
                                return Html::a(
                                    '<i class="fa fa-file-export"></i>',
                                    ['/dashboard/reports/outward', 'id' => $model->visit_id],
                                    ['class' => 'btn btn-sm btn-danger ms-1', 'title' => 'Print Outward Interchange', 'target' => '_blank']
                                );
                            }
                            return '';
                        },
                    ],
                    'contentOptions' => ['style' => 'width: 230px; text-align: center;'],
                ],
            ],
        ]); ?>
    </div>
</div>