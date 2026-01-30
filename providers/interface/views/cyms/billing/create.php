<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var dashboard\models\BillingRecords $model */

$this->title = 'Create Billing Records';
$this->params['breadcrumbs'][] = ['label' => 'Billing Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="billing-records-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
