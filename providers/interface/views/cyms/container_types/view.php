<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var dashboard\models\MasterContainerTypes $model */

$this->title = $model->type_id;
$this->params['breadcrumbs'][] = ['label' => 'Master Container Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="master-container-types-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'type_id' => $model->type_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'type_id' => $model->type_id], [
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
            'type_id',
            'iso_code',
            'size',
            'type_group',
            'description',
        ],
    ]) ?>

</div>
