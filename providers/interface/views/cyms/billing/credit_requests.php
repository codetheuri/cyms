<?php
use yii\helpers\Html;
use yii\bootstrap5\Modal;
use yii\widgets\ActiveForm;
use helpers\grid\GridView;

$this->title = 'Pending Credit Requests';
?>

<div class="block block-rounded content-card">
    <div class="block-header block-header-default">
        <h3 class="block-title">Credit Approvals Queue</h3>
    </div>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            
            // Container Info
            [
                'attribute' => 'visit.container_number',
                'label' => 'Container',
                'format' => 'raw',
                'value' => function($model) {
                    return Html::a($model->visit->container_number, ['view', 'id' => $model->bill_id], ['target'=>'_blank', 'data-pjax'=>0]);
                }
            ],
            
            // Amount
            [
                'attribute' => 'balance',
                'format' => ['currency', 'KES'],
                'contentOptions' => ['class' => 'text-danger fw-bold'],
            ],
            
            // Requester Info
            [
                'label' => 'Requested By',
                'format' => 'raw',
                'value' => function($model) {
                    // Safety check if relation is null
                    $username = $model->requester ? $model->requester->username : 'Unknown (ID: '.$model->requested_by.')';
                    $time = Yii::$app->formatter->asRelativeTime($model->requested_at);
                    return "<div>{$username}</div><small class='text-muted'>{$time}</small>";
                }
            ],
            
            // Reason
            [
                'attribute' => 'requester_note',
                'label' => 'Reason',
                'value' => function($model) {
                    return \yii\helpers\StringHelper::truncate($model->requester_note, 50);
                }
            ],

            // Actions
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{approve} {reject}',
                'buttons' => [
                    'approve' => function ($url, $model) {
                        return Html::button('<i class="fa fa-check"></i> Approve', [
                            'class' => 'btn btn-sm btn-success me-1',
                            'data-bs-toggle' => 'modal',
                            'data-bs-target' => '#approve-modal-' . $model->bill_id
                        ]);
                    },
                    'reject' => function ($url, $model) {
                        return Html::button('<i class="fa fa-times"></i> Reject', [
                            'class' => 'btn btn-sm btn-danger',
                            'data-bs-toggle' => 'modal',
                            'data-bs-target' => '#reject-modal-' . $model->bill_id
                        ]);
                    },
                ]
            ],
        ],
    ]); ?>
</div>

<?php foreach ($dataProvider->models as $model): ?>

    <div class="modal fade" id="approve-modal-<?= $model->bill_id ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success-light">
                    <h5 class="modal-title text-success">Approve Credit for <?= $model->visit->container_number ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <?php $form = ActiveForm::begin(['action' => ['approve-credit', 'id' => $model->bill_id]]); ?>
                <div class="modal-body">
                    <div class="alert alert-success bg-success-light border-0 mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fs-sm fw-bold text-uppercase text-muted">Total Due:</span>
                            <span class="fw-bold text-dark"><?= Yii::$app->formatter->asCurrency($model->balance, 'KES') ?></span>
                        </div>
                        <div class="fs-sm text-muted">Reason: "<?= Html::encode($model->requester_note) ?>"</div>
                    </div>
                    
                    <div class="mb-3">
                        <?= $form->field($model, 'atl_number')->textInput([
                            'required' => true, 
                            'placeholder' => 'Enter ATL Number (e.g. ATL-001)',
                            'class' => 'form-control form-control-lg fw-bold'
                        ])->label('Issue ATL Number') ?>
                        <div class="form-text fs-xs">This number authorizes the gate security to release the unit.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">Confirm Approval</button>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reject-modal-<?= $model->bill_id ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger-light">
                    <h5 class="modal-title text-danger">Reject Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <?php $form = ActiveForm::begin(['action' => ['reject-credit', 'id' => $model->bill_id]]); ?>
                <div class="modal-body">
                    <p class="mb-2">Reject credit for container <strong><?= $model->visit->container_number ?></strong>?</p>
                    <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4">Reject Request</button>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>

<?php endforeach; ?>