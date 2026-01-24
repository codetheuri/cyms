<?php

use dashboard\models\ContainerVisits;
use helpers\Html;
use yii\helpers\Url;
use helpers\grid\GridView;
use \DateTime;

/** @var yii\web\View $this */
/** @var dashboard\models\search\ContainerVisitsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Yard Inventory (Pending Exit)';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-visits-out-index row">
    <div class="col-md-12">
        <div class="block block-rounded content-card">
            <div class="block-header block-header-default">
                <h3 class="block-title fw-bold">
                    <i class="fa fa-truck-loading me-2 text-muted"></i><?= Html::encode($this->title) ?> 
                </h3>
            </div>
            <div class="block-content">
                
                <div class="container-visits-search my-3">
                    <?= $this->render('_search', ['model' => $searchModel]); ?>
                </div>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-striped table-hover table-vcenter'],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],

                        // 1. CONTAINER NUMBER
                        [
                            'attribute' => 'container_number',
                            'format' => 'raw',
                            'contentOptions' => ['class' => 'fs-5'],
                            'value' => function($model) {
                                return Html::tag('span', $model->container_number, ['class' => 'fw-bold text-primary']) . 
                                       '<br><small class="text-muted">' . ($model->containerType->iso_code ?? '-') . '</small>';
                            }
                        ],

                        // 2. TICKET
                        [
                            'attribute' => 'ticket_no_in',
                            'contentOptions' => ['class' => 'fs-xs font-monospace text-muted']
                        ],

                        // 3. DATE & TIME (Merged)
                        [
                            'attribute' => 'date_in',
                            'label' => 'Entry Time',
                            'format' => 'raw',
                            'value' => function($model) {
                                if ($model->date_in) {
                                    $date = Yii::$app->formatter->asDate($model->date_in, 'php:d M Y');
                                    $time = $model->time_in ? date('H:i', strtotime($model->time_in)) : '00:00';
                                    return "<div>{$date}</div><div class='fs-xs text-muted'><i class='fa fa-clock me-1'></i>{$time} hrs</div>";
                                }
                                return '-';
                            }
                        ],
                        
                        // 4. DAYS IN YARD (Integer Logic + Styling)
                        [
                            'label' => 'Duration',
                            'format' => 'raw',
                            'contentOptions' => ['class' => 'text-center'],
                            'headerOptions' => ['class' => 'text-center'],
                            'value' => function($model) {
                                if ($model->date_in) {
                                    $start = strtotime($model->date_in . ' ' . ($model->time_in ?: '00:00:00'));
                                    $diff = time() - $start;
                                    
                                    // Integer Logic: Part of a day counts as full day
                                    $days = floor($diff / 86400) + 1;

                                    // Color coding for long stays
                                    $badgeClass = ($days > 30) ? 'bg-danger' : (($days > 14) ? 'bg-warning' : 'bg-info');
                                    
                                    return "<span class='badge {$badgeClass} fs-sm'>{$days} Days</span>";
                                }
                                return '-';
                            }
                        ],
                        
                        // 5. STATUS
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function($model) {
                                return '<span class="badge bg-success"><i class="fa fa-check me-1"></i> READY</span>';
                            }
                        ],

                        // 6. ACTION BUTTON (Text instead of Icon)
                        [
                            'class' => \helpers\grid\ActionColumn::className(),
                            'template' => '{gate-out}',
                            'header' => 'Action',
                            'headerOptions' => ['width' => '15%'],
                            'contentOptions' => ['style' => 'text-align: center;'],
                            'buttons' => [
                                'gate-out' => function ($url, $model, $key) {
                                    // Using standard Html::a for full control over the button look
                                    return Html::a(
                                        '<i class="fa fa-truck-moving me-2"></i> Release Container',
                                        ['gate-out', 'id' => $model->visit_id],
                                        [
                                            'class' => 'btn btn-sm btn-alt-danger fw-bold w-100', // Wide button
                                            'title' => 'Process Gate Out',
                                            'data-pjax' => '0'
                                        ]
                                    );
                                },
                            ],
                        ],
                    ],
                ]); ?>

            </div>
        </div>
    </div>
</div>