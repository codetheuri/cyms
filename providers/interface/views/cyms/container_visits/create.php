<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var dashboard\models\ContainerVisits $model */

$this->title = 'Create Container Visits';
$this->params['breadcrumbs'][] = ['label' => 'Container Visits', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-visits-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
