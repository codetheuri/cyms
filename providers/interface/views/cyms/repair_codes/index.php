<?php

use yii\helpers\Html;
use helpers\grid\GridView;

$this->title = 'Master Repair Codes';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="block block-rounded content-card">
    <div class="block-header block-header-default">
        <h3 class="block-title"><?= Html::encode($this->title) ?></h3>
        <div class="block-options">
            <?= Html::a('<i class="fa fa-plus"></i> Add New Code', ['create'], ['class' => 'btn btn-sm btn-primary']) ?>
        </div>
    </div>
    
    <div class="block-content">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-striped table-hover table-vcenter'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                [
                    'attribute' => 'repair_code',
                    'label' => 'Code',
                    'contentOptions' => ['class' => 'fw-bold text-primary'],
                ],
                'description',
                [
                    'attribute' => 'standard_hours',
                    'label' => 'Std Hrs',
                    'contentOptions' => ['class' => 'text-center'],
                    'headerOptions' => ['class' => 'text-center'],
                ],
                [
                    'attribute' => 'material_cost',
                    'format' => ['currency', 'KES'],
                    'contentOptions' => ['class' => 'text-end'],
                ],
                [
                    'attribute' => 'labor_cost',
                    'format' => ['currency', 'KES'],
                    'contentOptions' => ['class' => 'text-end'],
                ],
                
                [
                    'class' => \helpers\grid\ActionColumn::class,
                    'template' => '{update} {delete}',
                ],
            ],
        ]); ?>
    </div>
</div>