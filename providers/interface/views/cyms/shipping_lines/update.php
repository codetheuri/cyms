<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var dashboard\models\MasterShippingLines $model */

$this->title = 'Update Master Shipping Lines: ' . $model->line_id;
$this->params['breadcrumbs'][] = ['label' => 'Master Shipping Lines', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->line_id, 'url' => ['view', 'line_id' => $model->line_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="master-shipping-lines-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
