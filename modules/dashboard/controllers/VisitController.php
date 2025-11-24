<?php

namespace dashboard\controllers;

use Yii;
use dashboard\models\ContainerVisits;
use dashboard\models\search\ContainerVisitsSearch;
use helpers\DashboardController;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use dashboard\models\MasterShippingLines;
use dashboard\models\SurveyDamages;
use dashboard\models\ContainerSurveys;
use dashboard\models\Model;
use dashboard\models\BillingRecords;
use dashboard\models\MasterContainerOwners;
use dashboard\models\MasterContainerTypes;

class VisitController extends DashboardController
{
    public function getViewPath()
    {

        return Yii::getAlias('@ui/views/cyms/container_visits');
    }
    public $layout = 'dashboard';

    public function actionIndex()

    {

        $searchModel = new ContainerVisitsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);


        $dataProvider->sort->defaultOrder = ['created_at' => SORT_DESC];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionGateIn()
    {
        $model = new ContainerVisits();
        $model->scenario = ContainerVisits::SCENARIO_GATE_IN;

        if ($model->isNewRecord) {
            $model->ticket_no_in = 'IN-' . time();
        }

        $model->date_in = date('Y-m-d');
        $model->time_in = date('H:i');

        if ($model->load(Yii::$app->request->post())) {
            $model->arrival_photo_file = UploadedFile::getInstance($model, 'arrival_photo_file');

            if ($model->validate()) {
                $path = $model->uploadArrivalPhoto();
                if ($path) {
                    $model->arrival_photo_path = $path;
                }
                $model->status = 'IN_YARD';

                if ($model->save(false)) {

                    $model->uploadDocuments();
                    $bill = new BillingRecords();
                    $bill->visit_id = $model->visit_id;


                    $bill->tariff_rate = Yii::$app->config->get('storage_rate_per_day') ?? 0;
                    $liftOn = Yii::$app->config->get('lift_on_charges') ?? 0;
                    $liftOff = Yii::$app->config->get('lift_off_charges') ?? 0;
                    $bill->lift_charges = $liftOn + $liftOff;


                    $bill->storage_days = 0;
                    $bill->repair_total = 0;
                    $bill->recalculateBalance();

                    Yii::$app->session->setFlash('success', 'Container Gated IN & Invoice Generated.');
                    return $this->redirect(['index']);
                }
            } else {
                Yii::$app->session->setFlash('error', 'Please fix the errors below.');
            }
        }

        $shippingLines = ArrayHelper::map(MasterShippingLines::find()->all(), 'line_id', 'line_name');

        $owners = ArrayHelper::map(MasterContainerOwners::find()->all(), 'owner_id', 'owner_name');


        $types = ArrayHelper::map(MasterContainerTypes::find()->all(), 'type_id', function ($m) {
            return $m->size . "' " . $m->type_group . ' (' . $m->iso_code . ')';
        });

        return $this->render('gate_in_form', [
            'model' => $model,
            'shippingLines' => $shippingLines,
            'owners' => $owners,
            'types' => $types,
        ]);
    }

    public function actionOutIndex()
    {
        $searchModel = new ContainerVisitsSearch();
        $queryParams = Yii::$app->request->queryParams;


        $queryParams['ContainerVisitsSearch']['status'] = ['SURVEYED'];

        $dataProvider = $searchModel->search($queryParams);

        return $this->render('out_index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionSurvey($visit_id)
    {
        $visit = $this->findModel($visit_id);


        $survey = ContainerSurveys::findOne(['visit_id' => $visit_id]);
        if (!$survey) {
            $survey = new ContainerSurveys();
            $survey->visit_id = $visit_id;
            $survey->survey_date = date('Y-m-d H:i:s');
            $survey->surveyor_name = Yii::$app->user->identity->username ?? 'System';
            $survey->approval_status = 'APPROVED';
        }
        $damages = $survey->getSurveyDamages()->all();
        if (empty($damages)) {
            $damages = [new SurveyDamages()];
        }

        if ($survey->load(Yii::$app->request->post())) {


            $oldDamagesIDs = \yii\helpers\ArrayHelper::map($damages, 'damage_id', 'damage_id');
            $damages = Model::createMultiple(SurveyDamages::class, $damages);
            Model::loadMultiple($damages, Yii::$app->request->post());


            $valid = $survey->validate();
            $valid = Model::validateMultiple($damages) && $valid;

            if ($valid) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    if ($survey->save(false)) {


                        foreach ($damages as $damage) {
                            $damage->survey_id = $survey->survey_id;
                            if (! ($flag = $damage->save(false))) {
                                $transaction->rollBack();
                                break;
                            }
                        }


                        if (!empty($oldDamagesIDs)) {
                            $deletedIDs = array_diff($oldDamagesIDs, array_filter(\yii\helpers\ArrayHelper::map($damages, 'damage_id', 'damage_id')));
                            if (!empty($deletedIDs)) {
                                SurveyDamages::deleteAll(['damage_id' => $deletedIDs]);
                            }
                        }


                        if ($visit->status === 'IN_YARD') {
                            $visit->status = 'SURVEYED';
                            $visit->save(false);
                        }
                        $bill = BillingRecords::findOne(['visit_id' => $visit->visit_id]);
                        if ($bill) {
                            $bill->recalculateBalance();
                        }

                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'Survey Saved Successfully.');
                        return $this->redirect(['index']);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'Transaction failed: ' . $e->getMessage());
                }
            }
        }

        return $this->render('survey_form', [
            'visit' => $visit,
            'survey' => $survey,
            'damages' => $damages,
        ]);
    }
    public function actionGateOut($id)
    {
        $model = $this->findModel($id);

        $bill = BillingRecords::findOne(['visit_id' => $id]);

        if (!$bill) {
            $bill = new BillingRecords();
            $bill->visit_id = $id;

            $bill->tariff_rate = Yii::$app->config->get('storage_rate_per_day') ?? 0;
            $liftOn = Yii::$app->config->get('lift_on_charges') ?? 0;
            $liftOff = Yii::$app->config->get('lift_off_charges') ?? 0;
            $bill->lift_charges = $liftOn + $liftOff;
        }


        $in = new \DateTime($model->date_in);
        $now = new \DateTime();
        $days = $in->diff($now)->days;
        $bill->storage_days = ($days < 1) ? 1 : $days;


        $bill->recalculateBalance();


        $isPaid = ($bill->status === 'PAID' || $bill->status === 'CREDIT' || $bill->balance <= 0.01);

        if (!$isPaid) {

            Yii::$app->session->setFlash('error', 'Container cannot be released. Outstanding Balance: ' . number_format($bill->balance, 2));
            return $this->redirect(['/dashboard/billing/view', 'id' => $bill->bill_id]);
        }

        $model->scenario = ContainerVisits::SCENARIO_GATE_OUT;

        if (empty($model->date_out)) {
            $model->date_out = date('Y-m-d');
            $model->time_out = date('H:i');
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->status = 'GATE_OUT';

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Container Released Successfully.');
                return $this->redirect(['out-index']);
            } else {
                $errors = implode('<br>', \yii\helpers\ArrayHelper::getColumn($model->getErrors(), 0));
                Yii::$app->session->setFlash('error', 'Failed to save Gate Out info: ' . $errors);
            }
        }

        return $this->render('gate_out_form', [
            'model' => $model,
        ]);
    }
    public function actionAjaxCreateOwner()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new \dashboard\models\MasterContainerOwners();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return ['success' => true, 'id' => $model->owner_id, 'name' => $model->owner_name];
        }
        return ['success' => false];
    }
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
    protected function findModel($id)
    {
        if (($model = ContainerVisits::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
