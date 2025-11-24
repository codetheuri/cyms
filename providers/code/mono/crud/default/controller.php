<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;


/** @var yii\web\View $this */
/** @var yii\gii\generators\crud\Generator $generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();
$namespace = StringHelper::dirname(ltrim($generator->controllerClass, '\\'));
$prefix =explode('\\',$namespace)[0];
echo "<?php\n";
?>

namespace <?= $namespace ?>;

use Yii;
use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
use yii\data\ActiveDataProvider;
<?php endif; ?>
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\web\NotFoundHttpException;

/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
    public $permissions = [
        '<?=$prefix?>-<?=$generator->getControllerID()?>-list'=>'View <?= $modelClass ?> List',
        '<?=$prefix?>-<?=$generator->getControllerID()?>-create'=>'Add <?= $modelClass ?>',
        '<?=$prefix?>-<?=$generator->getControllerID()?>-update'=>'Edit <?= $modelClass ?>',
        '<?=$prefix?>-<?=$generator->getControllerID()?>-delete'=>'Delete <?= $modelClass ?>',
        '<?=$prefix?>-<?=$generator->getControllerID()?>-restore'=>'Restore <?= $modelClass ?>',
        ];
    public function actionIndex()
    {
        Yii::$app->user->can('<?=$prefix?>-<?=$generator->getControllerID()?>-list');
<?php if (!empty($generator->searchModelClass)): ?>
        $searchModel = new <?= isset($searchModelAlias) ? $searchModelAlias : $searchModelClass ?>();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
<?php else: ?>
        $dataProvider = new ActiveDataProvider([
            'query' => <?= $modelClass ?>::find(),
            /*
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
<?php foreach ($pks as $pk): ?>
                    <?= "'$pk' => SORT_DESC,\n" ?>
<?php endforeach; ?>
                ]
            ],
            */
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
<?php endif; ?>
    }
    public function actionCreate()
    {
        Yii::$app->user->can('<?=$prefix?>-<?=$generator->getControllerID()?>-create');
        $model = new <?= $modelClass ?>();
        if ($this->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', '<?= $modelClass ?> created successfully');
                        return $this->redirect(['index']);
                    }
                }
            }
        } else {
            $model->loadDefaultValues();
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }
    public function actionUpdate(<?= $actionParams ?>)
    {
        Yii::$app->user->can('<?=$prefix?>-<?=$generator->getControllerID()?>-update');
        $model = $this->findModel(<?= $actionParams ?>);

        if ($this->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', '<?= $modelClass ?> updated successfully');
                        return $this->redirect(['index']);
                    }
                }
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }
    public function actionTrash(<?= $actionParams ?>)
    {
        $model = $this->findModel(<?= $actionParams ?>);
        if ($model->is_deleted) {
            Yii::$app->user->can('<?=$prefix?>-<?=$generator->getControllerID()?>-restore');
            $model->restore();
            Yii::$app->session->setFlash('success', '<?= $modelClass ?> has been restored');
        } else {
            Yii::$app->user->can('<?=$prefix?>-<?=$generator->getControllerID()?>-delete');
            $model->delete();
            Yii::$app->session->setFlash('success', '<?= $modelClass ?> has been deleted');
        }
        return $this->redirect(['index']);
    }
    protected function findModel(<?= $actionParams ?>)
    {
<?php
$condition = [];
foreach ($pks as $pk) {
    $condition[] = "'$pk' => \$$pk";
}
$condition = '[' . implode(', ', $condition) . ']';
?>
        if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(<?= $generator->generateString('The requested page does not exist.') ?>);
    }
}
