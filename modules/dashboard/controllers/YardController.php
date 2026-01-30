<?php

namespace dashboard\controllers;

use Yii;
use dashboard\models\YardSlots;
use dashboard\models\search\YardSlotsSearch;
use helpers\DashboardController;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use dashboard\models\ContainerVisits;

/**
 * YardController implements the CRUD actions for YardSlots model.
 */
class YardController extends DashboardController
{
    public $permissions = [
        'dashboard-yard-list' => 'View YardSlots List',
        'dashboard-yard-create' => 'Add YardSlots',
        'dashboard-yard-update' => 'Edit YardSlots',
        'dashboard-yard-delete' => 'Delete YardSlots',
        'dashboard-yard-restore' => 'Restore YardSlots',
    ];
    public function getViewPath()
    {
        return Yii::getAlias('@ui/views/cyms/yard');
    }
    public function actionIndex()
    {
     Yii::$app->user->can('dashboard-yard-list');
        $slots = YardSlots::find()
            ->with(['visit.containerOwner', 'visit.shippingLine']) 
            ->orderBy(['block' => SORT_ASC, 'row' => SORT_ASC, 'slot_name' => SORT_ASC])
            ->all();

      
        $yardMap = ArrayHelper::index($slots, null, 'block');

       
        $parkedIds = YardSlots::find()->select('current_visit_id')->where(['not', ['current_visit_id' => null]]);
        $unparkedContainers = ContainerVisits::find()
            ->where(['status' => ['IN_YARD', 'SURVEYED']])
            ->andWhere(['NOT IN', 'visit_id', $parkedIds])
            ->all();

        $containerDropdown = ArrayHelper::map($unparkedContainers, 'visit_id', function ($m) {
            return $m->container_number . ' (' . ($m->containerOwner->owner_name ?? 'Unknown') . ')';
        });

        return $this->render('index', [
            'yardMap' => $yardMap,
            'containerDropdown' => $containerDropdown,
        ]);
    }


    public function actionAssign($id)
    {
        Yii::$app->user->can('dashboard-yard-update');
        $slot = $this->findModel($id);
        if ($slot->load(Yii::$app->request->post())) {
            if ($slot->parkContainer($slot->current_visit_id)) {
                Yii::$app->session->setFlash('success', "Container assigned to {$slot->slot_name}");
            }
        }
        return $this->redirect(['index']);
    }


    public function actionUnpark($id)
    {
        Yii::$app->user->can('dashboard-yard-update');
        $slot = $this->findModel($id);
        $slot->unpark();
        Yii::$app->session->setFlash('warning', "Slot {$slot->slot_name} is now empty.");
        return $this->redirect(['index']);
    }


    public function actionGenerateMap()
    {
       
    }
    public function actionCreate()
    {
        Yii::$app->user->can('dashboard-yard-create');
        $model = new YardSlots();
        if ($this->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'YardSlots created successfully');
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
    public function actionUpdate($slot_id)
    {
        Yii::$app->user->can('dashboard-yard-update');
        $model = $this->findModel($slot_id);

        if ($this->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'YardSlots updated successfully');
                        return $this->redirect(['index']);
                    }
                }
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }
    public function actionTrash($slot_id)
    {
        $model = $this->findModel($slot_id);
        if ($model->is_deleted) {
            Yii::$app->user->can('dashboard-yard-restore');
            $model->restore();
            Yii::$app->session->setFlash('success', 'YardSlots has been restored');
        } else {
            Yii::$app->user->can('dashboard-yard-delete');
            $model->delete();
            Yii::$app->session->setFlash('success', 'YardSlots has been deleted');
        }
        return $this->redirect(['index']);
    }
    public function actionGenerate()
{
    if (YardSlots::find()->count() > 0) {
        return "Yard already initialized.";
    }

    for ($row = 1; $row <= 10; $row++) {
        for ($bay = 1; $bay <= 10; $bay++) {
            $slot = new YardSlots();
            $slot->block = 'B';
            $slot->row = $row;
            // $slot->bay = $bay;
            $slot->slot_name = sprintf("A-%02d-%02d", $row, $bay); // e.g. A-01-01
            $slot->save();
        }
    }
    return "Generated 100 slots for Block B.";
}
    protected function findModel($slot_id)
    {
        if (($model = YardSlots::findOne(['slot_id' => $slot_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
