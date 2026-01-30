<?php

use dashboard\models\MasterShippingLines;
use helpers\Html;
use yii\helpers\Url;
use helpers\grid\GridView;

/** @var yii\web\View $this */
/** @var dashboard\models\search\MasterShippingLinesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Master Shipping Lines';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="master-shipping-lines-index row">
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
              'text' => 'Create Master Shipping Lines',
              'theme' => 'primary',
              'visible' => Yii::$app->user->can('dashboard-shipping-line-create', true)
            ],
            'modal' => ['title' => 'New Master Shipping Lines']
          ]) ?>
          </div> 
        </div>
        <div class="block-content">     
    <div class="master-shipping-lines-search my-3">
    <?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'line_id',
            'line_code',
            'line_name',
            'contact_email:email',
            // 'created_at',
            //'updated_at',
            [
                'class' => \helpers\grid\ActionColumn::className(),
                'template' => '{update} {trash}',
                'headerOptions' => ['width' => '8%'],
                'contentOptions' => ['style'=>'text-align: center;'],
                 'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return Html::customButton(['type' => 'modal', 'url' => Url::toRoute(['update', 'line_id' => $model->line_id]), 'modal' => ['title' => 'Update  Master Shipping Lines'], 'appearence' => ['icon' => 'edit', 'theme' => 'info']]);
                    },
                    // 'trash' => function ($url, $model, $key) {
                    //     return $model->is_deleted !== 1 ?
                    //         Html::customButton(['type' => 'link', 'url' => Url::toRoute(['trash', 'line_id' => $model->line_id]),  'appearence' => ['icon' => 'trash', 'theme' => 'danger', 'data' => ['message' => 'Do you want to delete this master shipping lines?']]]) :
                    //         Html::customButton(['type' => 'link', 'url' => Url::toRoute(['trash', 'line_id' => $model->line_id]),  'appearence' => ['icon' => 'undo', 'theme' => 'warning', 'data' => ['message' => 'Do you want to restore this master shipping lines?']]]);
                    // },
                ],
                'visibleButtons' => [
                    'update' => Yii::$app->user->can('dashboard-shipping-line-update',true),
                    // 'trash' => function ($model){
                    //      return $model->is_deleted !== 1 ? 
                    //             Yii::$app->user->can('dashboard-shipping-line-delete',true) : 
                    //             Yii::$app->user->can('dashboard-shipping-line-restore',true);
                    // },
                ],
            ],
        ],
    ]); ?>


</div>
</div>
      </div>
    </div>