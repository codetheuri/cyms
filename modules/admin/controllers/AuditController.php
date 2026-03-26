<?php

namespace admin\controllers;

use Yii;
use helpers\models\AuditTrail;
use helpers\models\search\AuditTrailSearch;
use helpers\DashboardController;
use yii\web\NotFoundHttpException;

/**
 * AuditController implements the management UI for audit logs.
 */
class AuditController extends DashboardController
{
    //  public $layout = 'dashboard';
    /**
     * @var array RBAC permissions required for this controller
     */
    public $permissions = [
        'admin-audit-manage' => 'Manage Audit Trail',
    ];

    /**
     * {@inheritdoc}
     */
    public function getViewPath()
    {
        return Yii::getAlias('@ui/views/admin/audit');
    }

    /**
     * Lists all AuditTrail records.
     * @return mixed
     */
    public function actionIndex()
    {
        Yii::$app->user->can('admin-audit-manage');
        $searchModel = new AuditTrailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Export Audit Trail to Excel
     */
    public function actionExport()
    {
        Yii::$app->user->can('admin-audit-manage');
        $searchModel = new AuditTrailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;

        $models = $dataProvider->getModels();

        ob_clean();
        header("Content-type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: attachment; filename=System_Audit_Trail_" . date('Ymd_His') . ".xls");

        echo "<table border='1'>";
        echo "<thead style='background-color: #f1f1f1;'><tr>
                <th>ID</th>
                <th>Time of Action</th>
                <th>User / Executor</th>
                <th>Main Activity Area</th>
                <th>Business Action</th>
                <th>System Activity Type</th>
                <th>IP Address</th>
                <th>Affected Field(s)</th>
              </tr></thead>";
        echo "<tbody>";
        foreach ($models as $model) {
            $username = $model->user ? $model->user->username : 'Anonymous';
            echo "<tr>
                    <td>{$model->id}</td>
                    <td>" . date('Y-m-d H:i:s', $model->audit_time) . "</td>
                    <td>{$username}</td>
                    <td>" . \helpers\Html::encode($model->model_name) . "</td>
                    <td>" . \helpers\Html::encode($model->getFriendlyOperation()) . "</td>
                    <td>{$model->operation}</td>
                    <td>{$model->ip_address}</td>
                    <td>" . \helpers\Html::encode($model->field_name) . "</td>
                  </tr>";
        }
        echo "</tbody></table>";
        exit;
    }

    /**
     * View details of a specific audit entry.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if entry not found
     */
    public function actionView($id)
    {
        Yii::$app->user->can('admin-audit-manage');
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('view', [
                'model' => $model,
            ]);
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the AuditTrail model.
     * @param integer $id
     * @return AuditTrail the model
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = AuditTrail::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
