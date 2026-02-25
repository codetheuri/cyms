<?php

use helpers\Html;
use helpers\grid\GridView;
use yii\helpers\Url;
use dashboard\models\BillingRecords;
use dashboard\models\ContainerVisits; 

/* @var yii\web\View $this */
/* @var yii\data\ActiveDataProvider $dataProvider */
/* @var dashboard\models\search\ContainerVisitsSearch $searchModel */

$this->title = 'All Container Visits';

// Fetch the flagged count using the model function
$flaggedCount = ContainerVisits::getFlaggedCount();
?>

<div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center mb-4">
    <div class="flex-grow-1">
        <h1 class="h3 fw-bold mb-1"><?= Html::encode($this->title) ?></h1>
        <p class="fs-sm text-muted mb-0">Manage all container entries, surveys, and exits.</p>
    </div>
    <div class="mt-3 mt-sm-0">
        <?php if (Yii::$app->user->can('dashboard-visit-gate-in',true)): ?>
            <?= Html::a('<i class="fa fa-plus me-1"></i> New Gate IN', ['gate-in'], [
                'class' => 'btn btn-primary fw-bold px-4 py-2 shadow-sm'
            ]) ?>
        <?php endif; ?>
    </div>
</div>

<div class="block block-rounded shadow-sm">
    
    <ul class="nav nav-tabs nav-tabs-block nav-justified custom-tabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold py-3 fs-5" id="tab-all" data-bs-toggle="tab" data-bs-target="#content-all" role="tab">
                <i class="fa fa-list me-2 text-primary"></i> Master Records
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold py-3 fs-5 <?= $flaggedCount > 0 ? 'text-danger' : 'text-muted' ?>" id="tab-flagged" data-bs-toggle="tab" data-bs-target="#content-flagged" role="tab">
                <i class="fa fa-exclamation-triangle me-2 <?= $flaggedCount > 0 ? 'text-warning' : '' ?>"></i> Action Required / Flagged
                <?php if ($flaggedCount > 0): ?>
                    <span class="badge rounded-pill bg-danger ms-2 animate-pulse fs-sm"><?= $flaggedCount ?></span>
                <?php endif; ?>
            </button>
        </li>
    </ul>

    <div class="block-content tab-content p-0">

        <div class="tab-pane fade show active" id="content-all" role="tabpanel">
            <div class="p-3 bg-body-extra-light border-bottom">
                <?= $this->render('_search', ['model' => $searchModel]); ?>
            </div>

            <div class="table-responsive">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'emptyText' => '
                        <div class="text-center py-5">
                            <i class="fa fa-folder-open fa-3x text-muted opacity-50 mb-3"></i>
                            <h4 class="fw-bold text-dark">No containers found.</h4>
                            <p class="text-muted">We couldn\'t find any records matching your search.</p>
                            <a href="' . Url::to(['index']) . '" class="btn btn-sm btn-alt-primary rounded-pill px-4">
                                <i class="fa fa-undo me-1"></i> Reset Search
                            </a>
                        </div>
                    ',
                    'rowOptions' => function ($model) {
                        if (!empty($model->comments_in)) return ['class' => 'bg-warning-light border-start border-3 border-warning'];
                        return [];
                    },
                    'tableOptions' => ['class' => 'table table-striped table-hover table-vcenter mb-0'],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        ['attribute' => 'ticket_no_in', 'contentOptions' => ['class' => 'fs-xs text-muted font-monospace']],
                        [
                            'attribute' => 'container_number',
                            'contentOptions' => ['class' => 'fw-bold fs-5 text-primary'],
                            'format' => 'raw',
                            'value' => function ($model) {
                                $html = Html::encode($model->container_number);
                                if (!empty($model->comments_in)) {
                                    $html .= ' <i class="fa fa-flag text-danger ms-1" data-bs-toggle="tooltip" title="' . Html::encode($model->comments_in) . '"></i>';
                                }
                                return $html;
                            }
                        ],
                        [
                            'label' => 'Type',
                            'value' => function ($model) { return $model->containerType ? $model->containerType->iso_code : '-'; }
                        ],
                        [
                            'label' => 'Line',
                            'value' => function ($model) { return $model->shippingLine ? $model->shippingLine->line_code : '-'; }
                        ],
                        [
                            'label' => 'Date In',
                            'format' => 'raw',
                            'value' => function ($model) {
                                $date = Yii::$app->formatter->asDate($model->date_in, 'php:d M Y');
                                $time = $model->time_in ? date('H:i', strtotime($model->time_in)) : '';
                                return "<div>{$date}</div><div class='fs-xs text-muted'><i class='fa fa-clock me-1'></i>{$time} hrs</div>";
                            }
                        ],
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
                            'template' => '<div class="btn-group">{view} {flag} {trash}</div> <div class="btn-group">{dropdown}</div>',
                            'visibleButtons' => [
                                // HIDES BUTTONS IF NOT ADMIN (or lacks permission)
                                // 'flag'  => Yii::$app->user->can('dashboard-visit-update',true),
                                'trash' => Yii::$app->user->can('dashboard-visit-delete',true),
                            ],
                            'buttons' => [
                                'view' => function ($url, $model) {
                                    return Html::a('<i class="fa fa-eye"></i>', ['view', 'id' => $model->visit_id], [
                                        'class' => 'btn btn-sm btn-alt-secondary',
                                        'title' => 'View Details',
                                        'data-bs-toggle' => 'tooltip'
                                    ]);
                                },
                                'flag' => function ($url, $model) {
                                    return Html::customButton([
                                        'type' => 'modal',
                                        'url' => Url::to(['ajax-comment', 'id' => $model->visit_id]),
                                        'modal' => ['title' => 'Edit Flags / Comments', 'size' => 'md'],
                                        'appearence' => [
                                            'icon' => empty($model->comments_in) ? 'fa fa-flag' : 'fa fa-flag text-danger',
                                            'theme' => 'alt-secondary',
                                            'title' => 'Add/Edit Flag',
                                            'data' => ['toggle' => 'tooltip']
                                        ]
                                    ]);
                                },
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
                                            'appearence' => ['icon' => 'undo', 'theme' => 'warning', 'title' => 'Restore']
                                        ]);
                                    }
                                },
                                'dropdown' => function ($url, $model) {
                                    $links = '';
                                    
                                    // Protect Survey & Edit inside the dropdown
                                    if (Yii::$app->user->can('dashboard-visit-survey',true) && $model->status !== 'GATE_OUT') {
                                        $links .= '<li>' . Html::a('<i class="fa fa-clipboard-check me-2"></i> Survey', ['survey', 'visit_id' => $model->visit_id], ['class' => 'dropdown-item']) . '</li>';
                                    }
                                    if (Yii::$app->user->can('dashboard-visit-update',true) && ($model->status === 'IN_YARD' || $model->status === 'SURVEYED')) {
                                        $links .= '<li>' . Html::a('<i class="fa fa-pen me-2"></i> Full Edit', ['update', 'id' => $model->visit_id], ['class' => 'dropdown-item']) . '</li>';
                                    }
                                    
                                    $bill = BillingRecords::findOne(['visit_id' => $model->visit_id]);
                                    if ($bill) {
                                        $links .= '<li>' . Html::a('<i class="fa fa-file-invoice-dollar me-2 text-success"></i> View Invoice', ['/dashboard/billing/view', 'id' => $bill->bill_id], ['class' => 'dropdown-item', 'data-pjax' => 0]) . '</li>';
                                    }

                                    $links .= '<li><hr class="dropdown-divider"></li>';
                                    $links .= '<li>' . Html::a('<i class="fa fa-file-import me-2"></i> Print Inward', ['/dashboard/reports/inward', 'id' => $model->visit_id], ['class' => 'dropdown-item', 'target' => '_blank']) . '</li>';
                                    
                                    if ($model->status === 'GATE_OUT') {
                                        $links .= '<li>' . Html::a('<i class="fa fa-file-export me-2"></i> Print Outward', ['/dashboard/reports/outward', 'id' => $model->visit_id], ['class' => 'dropdown-item', 'target' => '_blank']) . '</li>';
                                    }

                                    if (empty($links)) return '';

                                    return '<div class="btn-group">
                                              <button type="button" class="btn btn-sm btn-alt-secondary dropdown-toggle" data-bs-toggle="dropdown">More</button>
                                              <ul class="dropdown-menu dropdown-menu-end">' . $links . '</ul>
                                            </div>';
                                }
                            ],
                            'contentOptions' => ['style' => 'width: 220px; text-align: right;'],
                        ],
                    ],
                ]); ?>
            </div>
        </div>

        <div class="tab-pane fade" id="content-flagged" role="tabpanel">
            <div class="p-3 bg-warning-light border-bottom border-warning d-flex align-items-center">
                <i class="fa fa-exclamation-circle fa-2x text-warning me-3"></i>
                <div>
                    <h5 class="mb-0 fw-bold text-warning-dark">Attention Required</h5>
                    <p class="mb-0 text-muted fs-sm">Containers currently in the yard with special instructions or warnings.</p>
                </div>
            </div>
            
            <div class="table-responsive">
                <?= GridView::widget([
                    'dataProvider' => ContainerVisits::getFlaggedDataProvider(),
                    'summary' => '<div class="p-3 fs-sm text-muted border-bottom">Showing {begin}-{end} of {totalCount} flagged items.</div>',
                    'emptyText' => '<div class="text-center py-5 text-success"><i class="fa fa-check-circle fa-4x mb-3 opacity-50"></i><h3 class="fw-bold">All Clear!</h3><p class="text-muted">There are no flagged containers in the yard right now.</p></div>',
                    'tableOptions' => ['class' => 'table table-striped table-hover table-vcenter mb-0'],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn', 'contentOptions' => ['style' => 'width: 50px; text-align: center;']],
                        [
                            'attribute' => 'container_number',
                            'format' => 'raw',
                            'contentOptions' => ['class' => 'fw-bold fs-5 text-primary', 'style' => 'width: 200px;'],
                            'value' => function ($model) {
                                return Html::encode($model->container_number) . "<br><span class='fs-xs font-monospace text-muted'>{$model->ticket_no_in}</span>";
                            }
                        ],
                        [
                            'attribute' => 'comments_in',
                            'label' => 'Special Instruction / Flag Reason',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return '<div class="p-2 fw-bold text-danger bg-danger-light border-start border-3 border-danger shadow-sm"><i class="fa fa-exclamation-triangle me-2"></i>' . Html::encode($model->comments_in) . '</div>';
                            }
                        ],
                        [
                            'label' => 'Arrival',
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'width: 150px;'],
                            'value' => function ($model) { 
                                $date = Yii::$app->formatter->asDate($model->date_in, 'php:d M Y');
                                return "<div class='fw-medium'>{$date}</div>";
                            }
                        ],
                        // [
                        //     'class'=> 'yii\grid\ActionColumn',
                        //     'header' => 'Edit',
                            
                        //     'template'=> 'edit' ,
                        //     'visibleButtons' => [
                        //         'edit' => Yii::$app->user->can('dashboard-visit-update',true),
                        //     ],
                        //     'buttons' => [
                        //         'edit' => function ($url, $model) {
                        //             if($model->status === 'IN_YARD' || $model->status === 'SURVEYED') {
                        //                 return Html::a('<i class="fa fa-pen me-1"></i> Edit',
                        //                  ['update', 'id' => $model->visit_id], 
                        //                  ['class' => 'btn btn-sm btn-alt-secondary']);
                        //             }
                        //         }
                        //     ]


                        // ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'Actions',
                            'template' => '{view} {resolve} {trash}',
                            'visibleButtons' => [
                                // ONLY ADMINS can resolve the flag from this tab
                                // 'resolve' => Yii::$app->user->can('dashboard-visit-update'), 
                                'trash' => Yii::$app->user->can('dashboard-visit-delete',true),
                            ],
                            'buttons' => [
                                'view' => function ($url, $model) {
                                    return Html::a('<i class="fa fa-eye me-1"></i> View', ['view', 'id' => $model->visit_id], ['class' => 'btn btn-sm btn-alt-secondary me-1']);
                                },
                                'resolve' => function ($url, $model) {
                                    return Html::customButton([
                                        'type' => 'modal',
                                        'url' => Url::to(['ajax-comment', 'id' => $model->visit_id]),
                                        'modal' => ['title' => 'Update / Resolve Flag', 'size' => 'md'],
                                        'appearence' => ['icon' => 'edit', 'theme' => 'warning', 'text' => 'Update', 'type' => 'iconText', 'size' => 'sm']
                                    ]);
                                },
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
                                            'appearence' => ['icon' => 'undo', 'theme' => 'warning', 'title' => 'Restore']
                                        ]);
                                    }
                                },

                            ],
                            'contentOptions' => ['style' => 'width: 180px; text-align: right;'],
                        ]
                    ]
                ]); ?>
            </div>
        </div>

    </div>
</div>

<style>
    /* Custom Styling to make Tabs very distinct */
    .custom-tabs {
        border-bottom: 2px solid #e5e7eb;
    }
    .custom-tabs .nav-link {
        background-color: #f3f4f6;
        color: #6b7280;
        border: none;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease-in-out;
    }
    .custom-tabs .nav-link:hover {
        background-color: #e5e7eb;
    }
    .custom-tabs .nav-link.active {
        background-color: #ffffff;
        color: #111827;
        border-bottom: 3px solid #0d6efd;
        box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .custom-tabs #tab-flagged.active {
        border-bottom: 3px solid #dc3545;
    }

    @keyframes pulse-red {
        0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); }
        70% { box-shadow: 0 0 0 6px rgba(220, 38, 38, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
    }
    .animate-pulse { animation: pulse-red 2s infinite; }
</style>