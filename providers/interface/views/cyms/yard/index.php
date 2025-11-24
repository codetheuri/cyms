<?php

use dashboard\models\YardSlots;
use helpers\Html;
use yii\helpers\Url;
use helpers\grid\GridView;

/** @var yii\web\View $this */
/** @var dashboard\models\search\YardSlotsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Yard Slots';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="yard-slots-index row">
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
              'text' => 'Create Yard Slots',
              'theme' => 'primary',
              'visible' => Yii::$app->user->can('dashboard-yard-create', true)
            ],
            'modal' => ['title' => 'New Yard Slots']
          ]) ?>
          </div> 
        </div>
        <div class="block-content">     
    <div class="yard-slots-search my-3">
    <?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'slot_id',
            'block',
            'row',
            'bay',
            'tier',
            //'slot_name',
            //'current_visit_id',
            [
                'class' => \helpers\grid\ActionColumn::className(),
                'template' => '{update} {trash}',
                'headerOptions' => ['width' => '8%'],
                'contentOptions' => ['style'=>'text-align: center;'],
                 'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return Html::customButton(['type' => 'modal', 'url' => Url::toRoute(['update', 'slot_id' => $model->slot_id]), 'modal' => ['title' => 'Update  Yard Slots'], 'appearence' => ['icon' => 'edit', 'theme' => 'info']]);
                    },
                    'trash' => function ($url, $model, $key) {
                        return $model->is_deleted !== 1 ?
                            Html::customButton(['type' => 'link', 'url' => Url::toRoute(['trash', 'slot_id' => $model->slot_id]),  'appearence' => ['icon' => 'trash', 'theme' => 'danger', 'data' => ['message' => 'Do you want to delete this yard slots?']]]) :
                            Html::customButton(['type' => 'link', 'url' => Url::toRoute(['trash', 'slot_id' => $model->slot_id]),  'appearence' => ['icon' => 'undo', 'theme' => 'warning', 'data' => ['message' => 'Do you want to restore this yard slots?']]]);
                    },
                ],
                'visibleButtons' => [
                    'update' => Yii::$app->user->can('dashboard-yard-update',true),
                    'trash' => function ($model){
                         return $model->is_deleted !== 1 ? 
                                Yii::$app->user->can('dashboard-yard-delete',true) : 
                                Yii::$app->user->can('dashboard-yard-restore',true);
                    },
                ],
            ],
        ],
    ]); ?>


</div>
</div>
      </div>
    </div>