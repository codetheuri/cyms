<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var dashboard\models\YardSlots $model */

$this->title = 'Create Yard Slots';
$this->params['breadcrumbs'][] = ['label' => 'Yard Slots', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="yard-slots-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
