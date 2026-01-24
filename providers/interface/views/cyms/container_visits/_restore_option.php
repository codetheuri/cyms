<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model dashboard\models\ContainerVisits */
?>

<div class="text-center py-4">
    <div class="mb-4">
        <i class="fa fa-exclamation-circle text-warning fa-4x mb-3"></i>
        <h4 class="fw-bold">Manage Deleted Record</h4>
        <p class="text-muted">
            Container <strong><?= Html::encode($model->container_number) ?></strong> is currently in the trash.
            <br>What would you like to do?
        </p>
    </div>

    <div class="row g-2">
        <div class="col-6">
            <a href="<?= Url::to(['trash', 'id' => $model->visit_id]) ?>" class="btn btn-alt-success w-100 py-3">
                <i class="fa fa-trash-restore me-1 mb-1 d-block fa-2x"></i>
                Restore Record
            </a>
        </div>
        <div class="col-6">
            <a href="<?= Url::to(['force-delete', 'id' => $model->visit_id]) ?>" 
               class="btn btn-alt-danger w-100 py-3"
               data-confirm="Are you sure? This will permanently remove the Visit, Bill, and Survey data. This cannot be undone."
               data-method="post">
                <i class="fa fa-times-circle me-1 mb-1 d-block fa-2x"></i>
                Delete Permanently
            </a>
        </div>
    </div>
    
    <div class="mt-4 pt-3 border-top">
        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
    </div>
</div>