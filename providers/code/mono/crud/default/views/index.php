<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/** @var yii\web\View $this */
/** @var yii\gii\generators\crud\Generator $generator */

$modelClass = StringHelper::basename($generator->modelClass);
$namespace = StringHelper::dirname(ltrim($generator->controllerClass, '\\'));
$prefix =explode('\\',$namespace)[0];
echo "<?php\n";
?>

use <?= $generator->modelClass ?>;
use helpers\Html;
use yii\helpers\Url;
use <?= $generator->indexWidgetType === 'grid' ? "helpers\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;' : '' ?>

/** @var yii\web\View $this */
<?= !empty($generator->searchModelClass) ? "/** @var " . ltrim($generator->searchModelClass, '\\') . " \$searchModel */\n" : '' ?>
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index row">
    <div class="col-md-12">
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title"><?= "<?= " ?>Html::encode($this->title) ?> </h3>
          <div class="block-options">
          <?= "<?= " ?> Html::customButton([
            'type' => 'modal',
            'url' => Url::to(['create']),
            'appearence' => [
              'type' => 'text',
              'text' => <?= $generator->generateString('Create ' . Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?>,
              'theme' => 'primary',
              'visible' => Yii::$app->user->can('<?=$prefix?>-<?=$generator->getControllerID()?>-create', true)
            ],
            'modal' => ['title' => <?= $generator->generateString('New ' . Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?>]
          ]) ?>
          </div> 
        </div>
        <div class="block-content">     
<?= $generator->enablePjax ? "    <?php Pjax::begin(); ?>\n" : '' ?>
<?php if(!empty($generator->searchModelClass)): ?>
    <div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-search my-3">
<?= "    <?= " ?>$this->render('_search', ['model' => $searchModel]); ?>
    </div>
<?php endif; ?>

<?php if ($generator->indexWidgetType === 'grid'): ?>
    <?= "<?= " ?>GridView::widget([
        'dataProvider' => $dataProvider,
        <?= !empty($generator->searchModelClass) ? "'columns' => [\n" : "'columns' => [\n"; ?>
            ['class' => 'yii\grid\SerialColumn'],

<?php
$count = 0;
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        if (++$count < 6) {
            echo "            '" . $name . "',\n";
        } else {
            echo "            //'" . $name . "',\n";
        }
    }
} else {
    foreach ($tableSchema->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        if (++$count < 6) {
            echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        } else {
            echo "            //'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        }
    }
}
?>
            [
                'class' => \helpers\grid\ActionColumn::className(),
                'template' => '{update} {trash}',
                'headerOptions' => ['width' => '8%'],
                'contentOptions' => ['style'=>'text-align: center;'],
                 'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return Html::customButton(['type' => 'modal', 'url' => Url::toRoute(['update', <?= $generator->generateUrlParams() ?>]), 'modal' => ['title' => 'Update  <?=Inflector::camel2words(StringHelper::basename($generator->modelClass))?>'], 'appearence' => ['icon' => 'edit', 'theme' => 'info']]);
                    },
                    'trash' => function ($url, $model, $key) {
                        return $model->is_deleted !== 1 ?
                            Html::customButton(['type' => 'link', 'url' => Url::toRoute(['trash', <?= $generator->generateUrlParams() ?>]),  'appearence' => ['icon' => 'trash', 'theme' => 'danger', 'data' => ['message' => 'Do you want to delete this <?=strtolower(Inflector::camel2words(StringHelper::basename($generator->modelClass)))?>?']]]) :
                            Html::customButton(['type' => 'link', 'url' => Url::toRoute(['trash', <?= $generator->generateUrlParams() ?>]),  'appearence' => ['icon' => 'undo', 'theme' => 'warning', 'data' => ['message' => 'Do you want to restore this <?=strtolower(Inflector::camel2words(StringHelper::basename($generator->modelClass)))?>?']]]);
                    },
                ],
                'visibleButtons' => [
                    'update' => Yii::$app->user->can('<?=$prefix?>-<?=$generator->getControllerID()?>-update',true),
                    'trash' => function ($model){
                         return $model->is_deleted !== 1 ? 
                                Yii::$app->user->can('<?=$prefix?>-<?=$generator->getControllerID()?>-delete',true) : 
                                Yii::$app->user->can('<?=$prefix?>-<?=$generator->getControllerID()?>-restore',true);
                    },
                ],
            ],
        ],
    ]); ?>
<?php else: ?>
    <?= "<?= " ?>ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => function ($model, $key, $index, $widget) {
            return Html::a(Html::encode($model-><?= $generator->getNameAttribute() ?>), ['view', <?= $generator->generateUrlParams() ?>]);
        },
    ]) ?>
<?php endif; ?>

<?= $generator->enablePjax ? "    <?php Pjax::end(); ?>\n" : '' ?>

</div>
</div>
      </div>
    </div>