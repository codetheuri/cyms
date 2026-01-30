<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $model dashboard\models\ContainerVisits */
?>

<div class="container-comment-form">
    <div class="alert alert-info py-2 mb-3">
        <i class="fa fa-info-circle me-1"></i> 
        Adding flag for Container: <strong><?= $model->container_number ?></strong>
    </div>

    <?php $form = ActiveForm::begin(); ?>

    <div class="mb-3">
        <?= $form->field($model, 'comments_in')->textarea([
            'rows' => 4, 
            'class' => 'form-control form-control-alt',
            'placeholder' => 'e.g. Police Case, Damaged, Mistake Entry...'
        ])->label('Flags / Instructions') ?>
    </div>

    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" name="clear_comment" id="clearComment">
        <label class="form-check-label text-muted" for="clearComment">
            Clear existing comments
        </label>
    </div>

    <div class="text-end">
        <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">Cancel</button>
        <?= Html::submitButton('Save Flag', ['class' => 'btn btn-sm btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>