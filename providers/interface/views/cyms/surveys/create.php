<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var dashboard\models\ContainerSurveys $model */

$this->title = 'Create Container Surveys';
$this->params['breadcrumbs'][] = ['label' => 'Container Surveys', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-surveys-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
