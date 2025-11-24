<?php

use helpers\Html;
use yii\rbac\Item;
use yii\helpers\Url;
use auth\hooks\Configs;
use helpers\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel mdm\admin\models\searchs\AuthItem */
/* @var $context mdm\admin\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$newPermissions = (new \auth\models\AuthItem(null))->scanPermissions();
$this->title = $labels['Items'];

$rules = array_keys(Configs::authManager()->getRules());
$rules = array_combine($rules, $rules);
?>
<div class="users-index row">
  <div class="col-md-12">
    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <h3 class="block-title"><?= Html::encode($this->title) ?> </h3>
        <div class="block-options">
          <?= Html::customButton([
            'type' => 'modal',
            'url' => Url::to(['create']),
            'appearence' => [
              'type' => 'text',
              'text' => 'Create ' . $labels['Item'],
              'theme' => 'primary',
              'visible' => Yii::$app->user->can('create-rbac-items', true)
            ],
            'modal' => ['title' => 'New ' . $labels['Item']]
          ]) ?>
          &nbsp;
          <?= Html::customButton([
            'type' => 'link',
            'url' => Url::to(['sync']),
            'appearence' => [
              'type' => 'text',
              'text' => 'Synch (' . count($newPermissions) . ') New ' . $labels['Items'],
              'theme' => 'success',
              'visible' => $context->getType() === Item::TYPE_PERMISSION && Yii::$app->user->can('sync-permissions', true) && count($newPermissions) > 0 ? true : false
            ],
          ]) ?>
        </div>
      </div>
      <div class="block-content">
        <div class="users-search my-3">
          <?= $this->render('_search', ['model' => $searchModel]); ?>
        </div>

        <?= GridView::widget([
          'dataProvider' => $dataProvider,
          'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
              'attribute' => 'name',
              'label' => $labels['Item'] . ' Code',
            ],
            [
              'attribute' => 'data',
              'label' => $labels['Item'] . ' Name',
            ],
            [
              'attribute' => 'ruleName',
              'label' => 'Rule Name',
              'filter' => $rules,
              'content' => function ($model) {
                return is_null($model->ruleName) ? 'No Rule Is Defined' : $model->ruleName;
              }
            ],
            [
              'attribute' => 'description',
              'label' => 'Description',
              'content' => function ($model) {
                return is_null($model->description) ? '-- no description --' : $model->description;
              }
            ],
            [
              'class' => \helpers\grid\ActionColumn::className(),
              'template' => '{update} {manage} {delete}',
              'headerOptions' => ['width' => '10%'],
              'contentOptions' => ['style' => 'text-align: center;'],
              'buttons' => [
                'manage' => function ($url, $model, $key) {
                  return Html::customButton(['type' => 'modal', 'url' => Url::to(['manage-role', 'id' => $key]), 'modal' => ['title' => 'Manage {' . $model->data . '} Role', 'size' => 'lg'], 'appearence' => ['icon' => 'cogs', 'theme' => 'success']]);
                },
                'update' => function ($url, $model, $key) {
                  return Html::customButton(['type' => 'modal', 'url' => Url::to(['update', 'id' => $key]), 'modal' => ['title' => 'Update  Users'], 'appearence' => ['icon' => 'edit', 'theme' => 'primary']]);
                },
                'delete' => function ($url, $model, $key) {
                  return Html::customButton(['type' => 'link', 'url' => Url::to(['delete', 'id' => $key]),  'appearence' => ['icon' => 'trash', 'theme' => 'danger', 'data' => ['message' => 'Do you want to delete this record?']]]);
                }
              ],
              'visibleButtons' => [
                'manage' => function ($model) {
                  return $model->type === Item::TYPE_ROLE && Yii::$app->user->can('manage-roles', true) ? true : false;
                },
                'update' => Yii::$app->user->can('update-rbac-items', true),
                'delete' => Yii::$app->user->can('delete-rbac-items', true),
              ],
            ],
          ],
        ]); ?>


      </div>
    </div>
  </div>
</div>