<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var dashboard\models\BillingRecords $model */

$this->title = 'Update Billing Records: ' . $model->bill_id;
$this->params['breadcrumbs'][] = ['label' => 'Billing Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->bill_id, 'url' => ['view', 'bill_id' => $model->bill_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="billing-records-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
