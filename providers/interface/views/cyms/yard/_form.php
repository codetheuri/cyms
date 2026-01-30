<?php

use helpers\Html;
use helpers\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var dashboard\models\YardSlots $model */
/** @var helpers\widgets\ActiveForm $form */
?>

<div class="yard-slots-form">
    <?php $form = ActiveForm::begin(['options' => ['data-pjax' => true]]);?>
    <div class="row">
        <div class="col-md-12">
          <?= $form->field($model, 'block')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'row')->textInput() ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'bay')->textInput() ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'tier')->textInput() ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'slot_name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'current_visit_id')->textInput() ?>
        </div>
    </div>
    <div class="block-content block-content-full text-center">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
