<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model dashboard\models\search\MasterContainerOwnersSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="master-container-owners-search">
<div class="clearfix">
     <div class="float-start">
         <?= Html::dropDownList('per-page',
        isset(Yii::$app->request->queryParams['per-page']) ? Yii::$app->request->queryParams['per-page'] : 25,
        Yii::$app->params['pageSize'],
        ['class'=>'form-select form-select-sm'])
        ?>

    </div>
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1 // Enables AJAX search if GridView uses Pjax
        ],
    ]); ?>

    <div class="row">
        <div class="col-md-8">
            <?= $form->field($model, 'globalSearch')->textInput([
                'placeholder' => 'Search by Owner Name, Contact or Email...',
                'class' => 'form-control'
            ])->label(false) ?>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <?= Html::submitButton('<i class="fa fa-search"></i> Search', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<i class="fa fa-times"></i> Reset', ['index'], ['class' => 'btn btn-alt-secondary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
</div>