<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model dashboard\models\Expenses */
?>

<div class="text-center py-4">
    <div class="mb-4">
        <i class="fa fa-broom text-warning fa-4x mb-3"></i>
        <h4 class="fw-bold">Management of Soft-Deleted Entry</h4>
        <p class="text-muted">
            This expense of <strong>KES <?= number_format($model->amount, 2) ?></strong> is currently in the trash.
            <br>Restoring it will include it back into all financial calculations.
        </p>
    </div>

    <div class="row g-2">
        <div class="col-6">
            <a href="<?= Url::to(['trash', 'id' => $model->expense_id]) ?>" class="btn btn-alt-success w-100 py-3">
                <i class="fa fa-trash-restore me-1 mb-1 d-block fa-2x"></i>
                Restore Record
            </a>
        </div>
        <div class="col-6">
            <a href="<?= Url::to(['force-delete', 'id' => $model->expense_id]) ?>" 
               class="btn btn-alt-danger w-100 py-3"
               data-confirm="ABSOLUTELY PERMANENT: This cannot be undone. physically remove this financial row?"
               data-method="post">
                <i class="fa fa-times-circle me-1 mb-1 d-block fa-2x"></i>
                Force Delete
            </a>
        </div>
    </div>
    
    <div class="mt-4 pt-3 border-top">
        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
    </div>
</div>
