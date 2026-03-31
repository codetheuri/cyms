<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model dashboard\models\MasterContainerOwners */

$this->title = 'Add New Client';
$this->params['breadcrumbs'][] = ['label' => 'Clients', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="block block-rounded content-card">
    
    <div class="block-content">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>