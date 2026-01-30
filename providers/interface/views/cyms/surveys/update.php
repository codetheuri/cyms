<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var dashboard\models\ContainerSurveys $model */

$this->title = 'Update Container Surveys: ' . $model->survey_id;
$this->params['breadcrumbs'][] = ['label' => 'Container Surveys', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->survey_id, 'url' => ['view', 'survey_id' => $model->survey_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="container-surveys-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
