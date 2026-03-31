<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model dashboard\models\ExpenseCategories */
?>

<div class="text-center py-4">
    <div class="mb-4">
        <i class="fa fa-folder-open text-warning fa-4x mb-3"></i>
        <h4 class="fw-bold">Management of Deleted Category</h4>
        <p class="text-muted">
             Category <strong><?= Html::encode($model->category_name) ?></strong> is in the trash.
             <br>No new expenses can be recorded under it while it is deleted.
        </p>
    </div>

    <div class="row g-2">
        <div class="col-6">
            <a href="<?= Url::to(['category-trash', 'id' => $model->category_id]) ?>" class="btn btn-alt-success w-100 py-3">
                <i class="fa fa-trash-restore me-1 mb-1 d-block fa-2x"></i>
                Restore Record
            </a>
        </div>
        <div class="col-6">
            <a href="<?= Url::to(['category-force-delete', 'id' => $model->category_id]) ?>" 
               class="btn btn-alt-danger w-100 py-3"
               data-confirm="PERMANENT REMOVAL: This will fail if there are any existing expenses tied to this category. Delete?"
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
