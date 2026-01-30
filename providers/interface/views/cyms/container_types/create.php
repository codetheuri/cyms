<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var dashboard\models\MasterContainerTypes $model */

$this->title = 'Create Master Container Types';
$this->params['breadcrumbs'][] = ['label' => 'Master Container Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="master-container-types-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
