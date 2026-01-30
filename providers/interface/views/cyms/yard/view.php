<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var dashboard\models\YardSlots $model */

$this->title = $model->slot_id;
$this->params['breadcrumbs'][] = ['label' => 'Yard Slots', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="yard-slots-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'slot_id' => $model->slot_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'slot_id' => $model->slot_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'slot_id',
            'block',
            'row',
            'bay',
            'tier',
            'slot_name',
            'current_visit_id',
        ],
    ]) ?>

</div>
