<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var dashboard\models\MasterContainerTypes $model */

$this->title = 'Update Master Container Types: ' . $model->type_id;
$this->params['breadcrumbs'][] = ['label' => 'Master Container Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->type_id, 'url' => ['view', 'type_id' => $model->type_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="master-container-types-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
