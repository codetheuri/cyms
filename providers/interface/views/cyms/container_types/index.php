<?php

use dashboard\models\MasterContainerTypes;
use helpers\Html;
use yii\helpers\Url;
use helpers\grid\GridView;

/** @var yii\web\View $this */
/** @var dashboard\models\search\MasterContainerTypesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Master Container Types';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="master-container-types-index row">
    <div class="col-md-12">
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title"><?= Html::encode($this->title) ?> </h3>
          <div class="block-options">
          <?=  Html::customButton([
            'type' => 'modal',
            'url' => Url::to(['create']),
            'appearence' => [
              'type' => 'text',
              'text' => 'Create Master Container Types',
              'theme' => 'primary',
              'visible' => Yii::$app->user->can('dashboard-container-type-create', true)
            ],
            'modal' => ['title' => 'New Master Container Types']
          ]) ?>
          </div> 
        </div>
        <div class="block-content">     
    <div class="master-container-types-search my-3">
    <?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'type_id',
            'iso_code',
            'size',
            'type_group',
            'description',
            [
                'class' => \helpers\grid\ActionColumn::className(),
                'template' => '{update} {trash}',
                'headerOptions' => ['width' => '8%'],
                'contentOptions' => ['style'=>'text-align: center;'],
                 'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return Html::customButton(['type' => 'modal', 'url' => Url::toRoute(['update', 'type_id' => $model->type_id]), 'modal' => ['title' => 'Update  Master Container Types'], 'appearence' => ['icon' => 'edit', 'theme' => 'info']]);
                    },
                    'trash' => function ($url, $model, $key) {
                        return $model->is_deleted !== 1 ?
                            Html::customButton(['type' => 'link', 'url' => Url::toRoute(['trash', 'type_id' => $model->type_id]),  'appearence' => ['icon' => 'trash', 'theme' => 'danger', 'data' => ['message' => 'Do you want to delete this master container types?']]]) :
                            Html::customButton(['type' => 'link', 'url' => Url::toRoute(['trash', 'type_id' => $model->type_id]),  'appearence' => ['icon' => 'undo', 'theme' => 'warning', 'data' => ['message' => 'Do you want to restore this master container types?']]]);
                    },
                ],
                'visibleButtons' => [
                    'update' => Yii::$app->user->can('dashboard-container-type-update',true),
                    'trash' => function ($model){
                         return $model->is_deleted !== 1 ? 
                                Yii::$app->user->can('dashboard-container-type-delete',true) : 
                                Yii::$app->user->can('dashboard-container-type-restore',true);
                    },
                ],
            ],
        ],
    ]); ?>


</div>
</div>
      </div>
    </div>