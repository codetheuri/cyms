<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\Pjax;
use auth\hooks\Configs;
use helpers\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model mdm\admin\models\AuthItem */
/* @var $form yii\widgets\ActiveForm */
/* @var $context mdm\admin\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$rules = Configs::authManager()->getRules();
$source = Json::htmlEncode(array_keys($rules));


?>
<?php Pjax::begin() ?>
<div class="auth-item-form">
    <?php $form = ActiveForm::begin(['options' => ['data-pjax' => true]]); ?>
    <div class="row">
        <div class="col-sm-12">
            <?= $form->field($model, 'name')->textInput(['maxlength' => 64])->label($labels['Item'] . ' Code') ?>
        </div>
        <div class="col-sm-12">
            <?= $form->field($model, 'data')->textInput(['maxlength' => 64])->label($labels['Item'] . ' Name') ?>
        </div>
        <div class="col-sm-12">
            <?= $form->field($model, 'ruleName')->textInput(['id' => 'rule_name']) ?>
        </div>
        <div class="col-sm-12">
            <?= $form->field($model, 'description')->textarea(['rows' => 2]) ?>
        </div>
    </div>
    <div class="block-content block-content-full text-center">
        <?php
        echo Html::submitButton($model->isNewRecord ? 'Create ' . $labels['Item'] : 'Update ' . $labels['Item'], [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
            'name' => 'submit-button'
        ])
        ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<?php Pjax::end() ?>