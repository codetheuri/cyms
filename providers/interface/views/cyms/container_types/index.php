<?php

use yii\helpers\Html;
use helpers\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Container Types & Rates';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="block block-rounded content-card">
    <div class="block-header block-header-default">
        <h3 class="block-title"><?= Html::encode($this->title) ?></h3>
        <div class="block-options">
            <?= Html::a('<i class="fa fa-plus me-1"></i> Add New Type', ['create'], ['class' => 'btn btn-sm btn-primary']) ?>
        </div>
    </div>
    
    <div class="block-content">
        <div class="table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-striped table-hover table-vcenter'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    [
                        'attribute' => 'iso_code',
                        'label' => 'ISO',
                        'contentOptions' => ['class' => 'fw-bold text-primary'],
                    ],
                    [
                        'attribute' => 'size',
                        'label' => 'Size',
                        'value' => function($m) { return $m->size . ' ft'; },
                        'contentOptions' => ['class' => 'text-center'],
                        'headerOptions' => ['class' => 'text-center'],
                    ],
                    'type_group',
                    'description',
                    
                    // --- THE PRICE COLUMN ---
                    [
                        'attribute' => 'daily_rate',
                        'label' => 'Storage Rate (Per Day)',
                        'format' => ['currency', 'KES'],
                        'contentOptions' => ['class' => 'text-end fw-bold text-success'],
                        'headerOptions' => ['class' => 'text-end'],
                    ],
                    
                    // --- ACTIONS COLUMN (Fixed Icons) ---
                    [
                        'class' => \helpers\grid\ActionColumn::className(),
                        'template' => '{update} {delete}',
                        'headerOptions' => ['width' => '120px', 'class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'buttons' => [
                            'update' => function ($url, $model, $key) {
                                return Html::a(
                                    '<i class="fa fa-pencil-alt"></i>',
                                    ['update', 'type_id' => $model->type_id],
                                    ['class' => 'btn btn-sm btn-alt-primary', 'title' => 'Edit', 'data-pjax' => 0]
                                );
                            },
                            'delete' => function ($url, $model, $key) {
                                return Html::a(
                                    '<i class="fa fa-trash"></i>',
                                    ['delete', 'id' => $model->type_id],
                                    [
                                        'class' => 'btn btn-sm btn-alt-danger ms-1',
                                        'title' => 'Delete',
                                        'data-pjax' => 0,
                                        'data-confirm' => 'Are you sure you want to delete this item?',
                                        'data-method' => 'post',
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