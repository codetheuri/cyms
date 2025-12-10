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
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title"><?= Html::encode($this->title) ?> </h3>
            </div>
            <div class="block-content">
                
                <div class="container-visits-search my-3">
                    <?= $this->render('_search', ['model' => $searchModel]); ?>
                </div>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    // We removed 'filterModel' => $searchModel here to fix the "Array to string" error
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],

                        [
                            'attribute' => 'container_number',
                            'contentOptions' => ['class' => 'fw-bold text-primary fs-5'],
                        ],
                        'ticket_no_in',
                        'date_in',
                        
                        // Custom Column: Days in Yard
                        [
                            'label' => 'Days in Yard',
                            'value' => function($model) {
                                if ($model->date_in) {
                                    $in = new DateTime($model->date_in);
                                    $now = new DateTime();
                                    $days = $in->diff($now)->days;
                                    if ($days<=1) {
                                        return '1 Day';
                                    }
                                    return $days . ' Days';
                                }
                                return '-';
                            }
                        ],
                        
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function($model) {
                                return '<span class="badge bg-success">' . $model->status . '</span>';
                            }
                        ],

                        [
                            'class' => \helpers\grid\ActionColumn::className(),
                            'template' => '{gate-out}',
                            'headerOptions' => ['width' => '10%'],
                            'contentOptions' => ['style'=>'text-align: center;'],
                            'buttons' => [
                                'gate-out' => function ($url, $model, $key) {
                                    return Html::customButton([
                                        'type' => 'link', // Or 'modal' if you want it in a popup
                                        'url' => Url::toRoute(['gate-out', 'id' => $model->visit_id]), 
                                        'appearence' => [
                                            'icon' => 'truck-moving', 
                                            'theme' => 'danger', 
                                            'title' => 'Release Container'
                                        ]
                                    ]);
                                },
                            ],
                        ],
                    ],
                ]); ?>

            </div>
        </div>
    </div>
</div>