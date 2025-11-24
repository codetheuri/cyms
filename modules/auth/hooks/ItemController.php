<?php

namespace auth\hooks;

use Yii;
use auth\models\AuthItem;
use auth\models\searches\AuthItemSearch;
use helpers\DashboardController;
use yii\web\NotFoundHttpException;
use yii\base\NotSupportedException;
use yii\filters\VerbFilter;
use yii\rbac\Item;
class ItemController extends DashboardController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $verbs = [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'assign' => ['post'],
                    'remove' => ['post'],
                ],
            ],
        ];
        return array_merge(parent::behaviors(), $verbs);
    }

    /**
     * Lists all AuthItem models.
     * @return mixed
     */
    public function actionIndex()
    {
        Yii::$app->user->can('list-rbac-items');
        $searchModel = new AuthItemSearch(['type' => $this->type]);
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }
    public function actionManageRole($id)
    {
        Yii::$app->user->can('manage-roles');
        $model = $this->findModel($id);
        return $this->renderAjax('_manage-role', ['model' => $model]);
    }
    public function actionCreate()
    {
        //Yii::$app->user->can('create-rbac-items');
        $model = new AuthItem(null);
        $model->type = $this->type;
        if ($this->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Record Added successfully');
                    return $this->redirect(['index']);
                }
            }
        }
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_form', [
                'model' => $model,
            ]);
        } else {
            return $this->redirect(['index']);
        }
    }
    public function actionUpdate($id)
    {
        Yii::$app->user->can('update-rbac-items');
        $model = $this->findModel($id);
        if ($this->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Record updated successfully');
                    return $this->redirect(['index']);
                }
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_form', [
                'model' => $model,
            ]);
        } else {
            return $this->redirect(['index']);
        }
    }
    public function actionDelete($id)
    {
        Yii::$app->user->can('delete-rbac-items');
        $model = $this->findModel($id);
        Configs::authManager()->remove($model->item);
        Helper::invalidate();
        Yii::$app->session->setFlash('success', 'Record deleted successfully');
        return $this->redirect(['index']);
    }
    public function actionAssign($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $model = $this->findModel($id);
        $success = $model->addChildren($items);
        Yii::$app->getResponse()->format = 'json';

        return array_merge($model->getItems(), ['success' => $success]);
    }
    public function actionRemove($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $model = $this->findModel($id);
        $success = $model->removeChildren($items);
        Yii::$app->getResponse()->format = 'json';

        return array_merge($model->getItems(), ['success' => $success]);
    }
    public function getViewPath()
    {
        return Yii::getAlias('@ui/views/iam/rbac');
    }
    public function labels()
    {
        throw new NotSupportedException(get_class($this) . ' does not support labels().');
    }
    public function getType()
    {
    }
    protected function findModel($id)
    {
        $auth = Configs::authManager();
        $item = $this->type === Item::TYPE_ROLE ? $auth->getRole($id) : $auth->getPermission($id);
        if ($item) {
            return new AuthItem($item);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
