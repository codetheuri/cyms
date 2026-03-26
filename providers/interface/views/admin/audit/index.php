<?php

use helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use helpers\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel helpers\models\search\AuditTrailSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'System Audit Trail';
?>

<div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center mb-4">
    <div class="flex-grow-1">
        <h1 class="h3 fw-bold mb-1"><?= Html::encode($this->title) ?></h1>
        <p class="fs-sm text-muted mb-0">Record of all record-level modifications for security and tracking.</p>
    </div>
</div>

<div class="block block-rounded shadow-sm">
    <div class="block-header block-header-default bg-body-light py-2 border-bottom">
        <h3 class="block-title fw-bold">
            <i class="fa fa-history me-2 text-primary"></i> Master Audit Logs
        </h3>
        <div class="block-options d-flex align-items-center">
            <a href="<?= Url::to(array_merge(['export'], Yii::$app->request->queryParams)) ?>" class="btn btn-sm btn-alt-success me-2 rounded-pill">
                <i class="fa fa-file-excel me-1"></i> Export Excel
            </a>
            <div class="me-3 fs-sm text-muted d-none d-md-block">
                Show: <?= Html::dropDownList('per-page',
                    Yii::$app->request->get('per-page', 25),
                    Yii::$app->params['pageSize'] ?? [10 => 10, 25 => 25, 50 => 50, 100 => 100],
                    [
                        'class' => 'form-select form-select-sm d-inline-block border-0 bg-body-dark fw-bold',
                        'style' => 'width: 75px;',
                        'onchange' => "let url = new URL(window.location.href); url.searchParams.set('per-page', this.value); window.location.href = url.toString();"
                    ]
                ) ?>
            </div>
            <form action="<?= Url::to(['index']) ?>" method="get" class="d-flex align-items-center">
                <div class="me-2 d-none d-lg-flex align-items-center bg-body-dark rounded-pill px-3 py-1 border shadow-sm">
                    <label class="mb-0 fs-xs text-uppercase text-muted me-2 border-end pe-2">Range:</label>
                    <input type="date" name="AuditTrailSearch[start_date]" class="form-control form-control-sm border-0 bg-transparent fs-xs p-0 text-primary fw-bold" style="width: 100px;" value="<?= Html::encode($searchModel->start_date) ?>" onchange="this.form.submit()">
                    <span class="mx-2 text-muted">-</span>
                    <input type="date" name="AuditTrailSearch[end_date]" class="form-control form-control-sm border-0 bg-transparent fs-xs p-0 text-primary fw-bold" style="width: 100px;" value="<?= Html::encode($searchModel->end_date) ?>" onchange="this.form.submit()">
                </div>
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" name="q" class="form-control rounded-start-pill text-center" placeholder="Global search..." value="<?= Html::encode(Yii::$app->request->get('q')) ?>">
                    <button type="submit" class="btn btn-alt-primary rounded-end-pill">
                        <i class="fa fa-search"></i>
                    </button>
                    <?php if (Yii::$app->request->get('q') || $searchModel->start_date || $searchModel->end_date): ?>
                        <a href="<?= Url::to(['index']) ?>" class="btn btn-alt-danger ms-1 rounded-pill" title="Clear All Filters">
                            <i class="fa fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <div class="block-content p-0">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-vcenter table-hover mb-0'],
            'layout' => "{items}\n<div class='p-3 border-top d-flex justify-content-between align-items-center'>{summary}{pager}</div>",
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'user_id',
                    'label' => 'Executor',
                    'content' => function($model) {
                        return $model->user?->username ?? '<span class="text-danger fw-bold">Anonymous</span>';
                    },
                    'filter' => \yii\helpers\ArrayHelper::map(\auth\models\User::find()->all(), 'user_id', 'username'),
                ],
                [
                    'attribute' => 'model_name',
                    'label' => 'Entity',
                    'value' => 'model_name',
                ],
                [
                    'attribute' => 'operation',
                    'label' => 'Operation',
                    'format' => 'raw',
                    'filter' => [
                        'INSERT' => 'INSERT',
                        'UPDATE' => 'UPDATE',
                        'DELETE' => 'DELETE',
                        'RESTORE' => 'RESTORE',
                        'LOGIN' => 'LOGIN',
                        'LOGOUT' => 'LOGOUT',
                        'PRINT' => 'PRINT',
                        'CHANGE_PASSWORD' => 'PWD_CHANGE',
                        'RESET_PASSWORD' => 'PWD_RESET',
                    ],
                    'value' => function($model) {
                        $color = match($model->operation) {
                            'INSERT' => 'success',
                            'UPDATE' => 'info',
                            'DELETE' => 'danger',
                            'RESTORE' => 'warning',
                            'LOGIN', 'LOGOUT' => 'primary',
                            'PRINT' => 'dark',
                            default => 'secondary'
                        };
                        return "<span class='badge bg-$color'>" . Html::encode($model->getFriendlyOperation()) . "</span>";
                    }
                ],
                [
                    'attribute' => 'field_name',
                    'label' => 'Field',
                    'value' => 'field_name',
                ],
                [
                    'attribute' => 'old_value',
                    'label' => 'Old Data',
                    'contentOptions' => ['style' => 'max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'],
                    'value' => function($model) {
                         $data = json_decode($model->old_value, true);
                         if (is_array($data) && count($data) > 0) return '{' . count($data) . ' fields}';
                         return $model->old_value ?: '-';
                    }
                ],
                [
                    'attribute' => 'new_value',
                    'label' => 'New Data',
                    'contentOptions' => ['style' => 'max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'],
                    'value' => function($model) {
                         $data = json_decode($model->new_value, true);
                         if (is_array($data) && count($data) > 0) return '{' . count($data) . ' fields}';
                         return $model->new_value ?: '-';
                    }
                ],
                [
                    'attribute' => 'audit_time',
                    'label' => 'When',
                    'headerOptions' => ['width' => '150px'],
                    'filter' => false, // Disable filter for timestamp unless date picker added
                    'content' => function ($model) {
                        return Html::timeAgo($model->audit_time);
                    }
                ],
                [
                    'class' => ActionColumn::class,
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('<i class="fa fa-eye"></i>', '#', [
                                'class' => 'btn btn-sm btn-alt-info loadModal',
                                'title' => 'Detailed Audit Entry',
                                'data-payload' => Url::to(['view', 'id' => $key]),
                                'data-title' => 'Audit Entry #' . $key,
                                'data-size' => 'modal-xl',
                                'onclick' => "return false;"
                            ]);
                        },
                    ],
                ],
            ],
        ]); ?>
    </div>
</div>
