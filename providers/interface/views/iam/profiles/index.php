<?php

use helpers\Html;
use yii\helpers\Url;
use helpers\grid\GridView;

/** @var yii\web\View $this */
/** @var dashboard\models\UsersSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'List of User Accounts';
?>
<div class="user-index row">
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
              'text' => 'Create User',
              'theme' => 'primary',
              'visible' => Yii::$app->user->can('dashboard-profile-create', true)
            ],
            'modal' => ['title' => 'New User']
          ]) ?>
        </div>
      </div>
      <div class="block-content">
        <div class="user-search my-3">
          <?= $this->render('_search', ['model' => $searchModel]); ?>
        </div>

        <?= GridView::widget([
          'dataProvider' => $dataProvider,
          'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            // [
            //   "attribute" => "profile.full_name",
            //   'enableSorting' => true,
            //   "content" => function ($model) {
            //     return $model->profile->full_name;
            //   }
            // ],
            // [
            //   "attribute" => "profile.email_address",
            //   'enableSorting' => true,
            //   "content" => function ($model) {
            //     return $model->profile->email_address;
            //   }
            // ],
            'username',
            [
              "attribute" => "status",
              "content" => function ($model) {
                return $model->recordStatus;
              }
            ],
            //'is_deleted',
            //'created_at',
            //'updated_at',
            [
              'class' => \helpers\grid\ActionColumn::className(),
              'template' => '{manage} {trash}',
              'headerOptions' => ['width' => '10%'],
              'contentOptions' => ['style' => 'text-align: center;'],
              'buttons' => [
                'manage' => function ($url, $model, $key) {
                  return Html::customButton(['type' => 'modal', 'url' => Url::to(['assignment', 'id' => $key]), 'modal' => ['title' => 'Manage {' . $model->username . '} Roles', 'size' => 'lg'], 'appearence' => ['icon' => 'user-shield', 'theme' => 'success']]);
                },
                // 'update' => function ($url, $model, $key) {
                //   return Html::customButton(['type' => 'modal', 'url' => Url::toRoute(['change-password', 'theme'=> 'primary', 'user_id' => $model->user_id]), 'modal' => ['title' => 'Update  User'], 'appearence' => ['icon' => 'edit', 'theme' => 'info']]);
                // },
                'trash' => function ($url, $model, $key) {
                  return $model->is_deleted !== 1 ?
                    Html::customButton(['type' => 'link', 'url' => Url::toRoute(['trash', 'user_id' => $model->user_id]),  'appearence' => ['icon' => 'trash', 'theme' => 'danger', 'data' => ['message' => 'Do you want to delete this user?']]]) :
                    Html::customButton(['type' => 'link', 'url' => Url::toRoute(['trash', 'user_id' => $model->user_id]),  'appearence' => ['icon' => 'undo', 'theme' => 'warning', 'data' => ['message' => 'Do you want to restore this user?']]]);
                },
              ],
              'visibleButtons' => [
                'manage' => function ($model) {
                  return Yii::$app->user->can('dashboard-profile-assignment', true) && $model->is_deleted !== 1 ? true : false;
                },
                // 'update' => function ($model) {
                //   return Yii::$app->user->can('dashboard-profile-update', true) && $model->is_deleted !== 1 ? true : false;
                // },
                'trash' => function ($model) {
                  return $model->is_deleted !== 1 ?
                    Yii::$app->user->can('dashboard-profile-delete', true) :
                    Yii::$app->user->can('dashboard-profile-restore', true);
                },
              ],
            ],
          ],
        ]); ?>

      </div>
    </div>
  </div>
</div>