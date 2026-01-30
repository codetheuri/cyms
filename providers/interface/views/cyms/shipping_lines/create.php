<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var dashboard\models\MasterShippingLines $model */

$this->title = 'Create Master Shipping Lines';
$this->params['breadcrumbs'][] = ['label' => 'Master Shipping Lines', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="master-shipping-lines-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
