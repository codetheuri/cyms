<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var dashboard\models\YardSlots $model */

$this->title = 'Update Yard Slots: ' . $model->slot_id;
$this->params['breadcrumbs'][] = ['label' => 'Yard Slots', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->slot_id, 'url' => ['view', 'slot_id' => $model->slot_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="yard-slots-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
