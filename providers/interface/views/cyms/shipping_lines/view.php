<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var dashboard\models\MasterShippingLines $model */

$this->title = $model->line_id;
$this->params['breadcrumbs'][] = ['label' => 'Master Shipping Lines', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="master-shipping-lines-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'line_id' => $model->line_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'line_id' => $model->line_id], [
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
            'line_id',
            'line_code',
            'line_name',
            'contact_email:email',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
