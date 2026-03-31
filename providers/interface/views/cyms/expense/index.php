<?php

use helpers\Html;
use yii\helpers\Url;
use helpers\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var dashboard\models\search\ExpensesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Yard Expenses';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-money-bill-transfer me-1 text-muted"></i> <?= Html::encode($this->title) ?>
                </h3>
                <div class="block-options">
                    <?= Html::customButton([
                        'type' => 'modal',
                        'url' => Url::to(['create']),
                        'modal' => ['title' => 'Log New Yard Expense', 'size' => 'lg'],
                        'appearence' => ['icon' => 'plus', 'theme' => 'alt-success', 'text' => 'Record Expense', 'type' => 'iconText', 'size' => 'sm']
                    ]) ?>
                    <a href="<?= Url::to(['categories']) ?>" class="btn btn-sm btn-alt-primary ms-2 shadow-sm">
                        <i class="fa fa-tags me-1"></i> Categories
                    </a>
                    <a href="<?= Url::to(['summary']) ?>" class="btn btn-sm btn-alt-info ms-2 shadow-sm">
                         <i class="fa fa-chart-line me-1"></i> Summary
                    </a>
                </div>
            </div>
            
            <div class="block-content">
                <?php Pjax::begin(['id' => 'expenses-pjax']); ?>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <form method="get" class="d-flex align-items-center w-50">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent border-0"><i class="fa fa-search"></i></span>
                            <input type="text" name="ExpensesSearch[globalSearch]" class="form-control border-0 bg-light" placeholder="Search by recipient, ref, or notes..." value="<?= Html::encode($searchModel->globalSearch) ?>">
                        </div>
                    </form>
                </div>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-hover table-vcenter'],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'expense_date',
                            'format' => ['date', 'php:d M Y'],
                            'contentOptions' => ['class' => 'fw-bold'],
                        ],
                        [
                            'attribute' => 'category_id',
                            'value' => 'category.category_name',
                        ],
                        [
                            'attribute' => 'amount',
                            'format' => ['currency', 'KES'],
                            'contentOptions' => ['class' => 'fw-bold text-danger text-end'],
                            'headerOptions' => ['class' => 'text-end'],
                        ],
                        'reference_no',
                        'payment_method',
                        [
                            'class' => \helpers\grid\ActionColumn::className(),
                            'template' => '{update} {trash}',
                            'buttons' => [
                                'update' => function ($url, $model) {
                                    return Html::customButton([
                                        'type' => 'modal',
                                        'url' => Url::to(['update', 'id' => $model->expense_id]),
                                        'modal' => ['title' => 'Modify Expense Entry', 'size' => 'lg'],
                                        'appearence' => ['icon' => 'pencil-alt', 'theme' => 'alt-primary', 'size' => 'sm']
                                    ]);
                                },
                                'trash' => function ($url, $model) {
                                    if ($model->is_deleted !== 1) {
                                        return Html::a('<i class="fa fa-trash"></i>', ['trash', 'id' => $model->expense_id], [
                                            'class' => 'btn btn-sm btn-alt-danger',
                                            'title' => 'Move to Trash',
                                            'data' => ['confirm' => 'This record will be excluded from all calculations. Proceed?', 'method' => 'post']
                                        ]);
                                    } else {
                                        return Html::customButton([
                                            'type' => 'modal',
                                            'url' => Url::to(['restore-option', 'id' => $model->expense_id]),
                                            'modal' => ['title' => 'Management Options', 'size' => 'md'],
                                            'appearence' => ['icon' => 'undo', 'theme' => 'warning', 'title' => 'Restore Ledger Item']
                                        ]);
                                    }
                                },
                            ],
                        ],
                    ],
                ]); ?>
                
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>
