<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model dashboard\models\MasterContainerOwners */

$this->title = 'Update Client: ' . $model->owner_name;
$this->params['breadcrumbs'][] = ['label' => 'Clients', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->owner_name, 'url' => ['view', 'id' => $model->owner_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="block block-rounded content-card">
    <div class="block-header block-header-default">
        <h3 class="block-title"><?= Html::encode($this->title) ?></h3>
    </div>
    <div class="block-content">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>