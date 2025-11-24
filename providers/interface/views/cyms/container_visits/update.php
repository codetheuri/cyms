<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var dashboard\models\ContainerVisits $model */

$this->title = 'Update Container Visits: ' . $model->visit_id;
$this->params['breadcrumbs'][] = ['label' => 'Container Visits', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->visit_id, 'url' => ['view', 'visit_id' => $model->visit_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="container-visits-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
