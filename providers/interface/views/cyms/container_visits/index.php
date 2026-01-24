<?php

use helpers\Html;
use helpers\grid\GridView;
use yii\helpers\Url;

/* @var yii\web\View $this */
/* @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Gate IN Records';
?>

<div class="block block-rounded content-card">
    <div class="block-header block-header-default">
        <h3 class="block-title fw-bold"><?= Html::encode($this->title) ?></h3>
        <div class="block-options">
            <?= Html::customButton([
                'type' => 'link',
                'url' => Url::to(['gate-in']),
                'appearence' => [
                    'type' => 'iconText',
                    'size' => 'lg',
                    'icon' => 'fa fa-plus me-1',
                    'text' => 'Gate IN Container',
                    'theme' => 'primary',
                    'visible' => true,
                ],
            ]) ?>
        </div>
    </div>
    <div class="block-content">
        <div class="user-search my-3">
            <?= $this->render('_search', ['model' => $searchModel]); ?>
        </div>
        
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            // 'filterModel' => $searchModel, // Uncomment if you want filters back
            'rowOptions' => function ($model) {
                if (!empty($model->comments_in)) {
                    return ['class' => 'table-warning fw-bold border-start border-3 border-warning'];
                }
                return [];
            },
            'tableOptions' => ['class' => 'table table-striped table-hover table-vcenter'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                
                // 1. TICKET
                [
                    'attribute' => 'ticket_no_in',
                    'contentOptions' => ['class' => 'fs-xs text-muted font-monospace']
                ],

                // 2. CONTAINER (With Flag Indicator)
                [
                    'attribute' => 'container_number',
                    'contentOptions' => ['class' => 'fw-bold fs-5 text-primary'],
                    'format' => 'raw',
                    'value' => function ($model) {
                        $html = Html::encode($model->container_number);
                        if (!empty($model->comments_in)) {
                            // Show a red flag if comments exist
                            $html .= ' <i class="fa fa-flag text-danger ms-1" data-bs-toggle="tooltip" title="' . Html::encode($model->comments_in) . '"></i>';
                        }
                        return $html;
                    }
                ],

                // 3. TYPE
                [
                    'label' => 'Type',
                    'attribute' => 'container_type_id',
                    'value' => function ($model) {
                        return $model->containerType ? $model->containerType->iso_code : '-';
                    }
                ],

                // 4. LINE
                [
                    'label' => 'Line',
                    'attribute' => 'shipping_line_id',
                    'value' => function ($model) {
                        return $model->shippingLine ? $model->shippingLine->line_code : '-';
                    }
                ],

                // 5. OWNER / TRUCK
                [
                    'label' => 'Owner',
                    'attribute' => 'truck_owner_name_in',
                    'value' => function ($model) {
                        return $model->containerOwner ? \yii\helpers\StringHelper::truncate($model->containerOwner->owner_name, 15) : '-';
                    }
                ],

                // 6. DATE & TIME (Merged)
                [
                    'label' => 'Date In',
                    'attribute' => 'date_in',
                    'format' => 'raw',
                    'value' => function($model) {
                        $date = Yii::$app->formatter->asDate($model->date_in, 'php:d M Y');
                        $time = $model->time_in ? date('H:i', strtotime($model->time_in)) : '';
                        return "<div>{$date}</div><div class='fs-xs text-muted'><i class='fa fa-clock me-1'></i>{$time} hrs</div>";
                    }
                ],

                // 7. STATUS
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
                
                // 8. ACTIONS (Cleaned Up)
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => 'Actions',
                    'template' => '<div class="btn-group">{view} {flag} {trash}</div> <div class="btn-group">{dropdown}</div>',
                    'buttons' => [
                        
                        // VIEW
                        'view' => function ($url, $model) {
                            return Html::a('<i class="fa fa-eye"></i>', ['view', 'id' => $model->visit_id], [
                                'class' => 'btn btn-sm btn-alt-secondary', 
                                'title' => 'View Details',
                                'data-bs-toggle' => 'tooltip'
                            ]);
                        },

                        // QUICK COMMENT / FLAG (Modal)
                        'flag' => function ($url, $model) {
                            return Html::customButton([
                                'type' => 'modal',
                                'url' => Url::to(['ajax-comment', 'id' => $model->visit_id]),
                                'modal' => ['title' => 'Edit Flags / Comments', 'size' => 'md'],
                                'appearence' => [
                                    'icon' => empty($model->comments_in) ? 'fa fa-flag' : 'fa fa-flag text-danger',
                                    'theme' => 'alt-secondary', // Light gray button
                                    'title' => 'Add/Edit Flag',
                                    'data' => ['toggle' => 'tooltip']
                                ]
                            ]);
                        },

                        // TRASH
                        'trash' => function ($url, $model) {
                            if ($model->is_deleted !== 1) {
                                return Html::a('<i class="fa fa-trash"></i>', ['trash', 'id' => $model->visit_id], [
                                    'class' => 'btn btn-sm btn-alt-danger',
                                    'title' => 'Trash',
                                    'data' => ['confirm' => 'Move to trash?', 'method' => 'post']
                                ]);
                            } else {
                                return Html::customButton([
                                    'type' => 'modal',
                                    'url' => Url::to(['restore-option', 'id' => $model->visit_id]),
                                    'modal' => ['title' => 'Trash Options', 'size' => 'md'],
                                    'appearence' => [
                                        'icon' => 'undo',
                                        'theme' => 'warning',
                                        'title' => 'Restore',
                                    ]
                                ]);
                            }
                        },

                        // DROPDOWN FOR "EXTRAS" (Survey, Prints)
                        'dropdown' => function ($url, $model) {
                            $links = '';

                            // Survey Link
                            if ($model->status !== 'GATE_OUT') {
                                $links .= '<li>' . Html::a(
                                    '<i class="fa fa-clipboard-check me-2"></i> Survey',
                                    ['survey', 'visit_id' => $model->visit_id],
                                    ['class' => 'dropdown-item']
                                ) . '</li>';
                            }
                            
                            // Edit Link (Full Edit)
                            if ($model->status === 'IN_YARD') {
                                $links .= '<li>' . Html::a(
                                    '<i class="fa fa-pen me-2"></i> Full Edit',
                                    ['update', 'id' => $model->visit_id],
                                    ['class' => 'dropdown-item']
                                ) . '</li>';
                            }

                            // Divider
                            if ($links) $links .= '<li><hr class="dropdown-divider"></li>';

                            // Print Inward
                            if ($model->status === 'SURVEYED' || $model->status === 'GATE_OUT') {
                                $links .= '<li>' . Html::a(
                                    '<i class="fa fa-file-import me-2"></i> Print Inward',
                                    ['/dashboard/reports/inward', 'id' => $model->visit_id],
                                    ['class' => 'dropdown-item', 'target' => '_blank']
                                ) . '</li>';
                            }

                            // Print Outward
                            if ($model->status === 'GATE_OUT') {
                                $links .= '<li>' . Html::a(
                                    '<i class="fa fa-file-export me-2"></i> Print Outward',
                                    ['/dashboard/reports/outward', 'id' => $model->visit_id],
                                    ['class' => 'dropdown-item', 'target' => '_blank']
                                ) . '</li>';
                            }
                            
                            if (empty($links)) return '';

                            return '<div class="btn-group">
                                      <button type="button" class="btn btn-sm btn-alt-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                        More
                                      </button>
                                      <ul class="dropdown-menu dropdown-menu-end">' . $links . '</ul>
                                    </div>';
                        }
                    ],
                    'contentOptions' => ['style' => 'width: 200px; text-align: right;'],
                ],
            ],
        ]); ?>
    </div>
</div>