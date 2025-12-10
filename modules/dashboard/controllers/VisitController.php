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
use dashboard\models\YardSlots;

class VisitController extends DashboardController
{
    public function getViewPath()
    {

        return Yii::getAlias('@ui/views/cyms/container_visits');
    }
    public $layout = 'dashboard';
    public $permissions = [
        'dashboard-visit-list' => 'View Container Visits List',
        'dashboard-visit-gate-in' => 'Gate In Container',
        'dashboard-visit-gate-out' => 'Gate Out Container',
        'dashboard-visit-survey' => 'Survey Container',
        'dashboard-visit-view' => 'View Container Visit Details',
    ];

    public function actionIndex()

    {
    //    Yii::$app->user->can('dashboard-visit-list');
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
        // Yii::$app->user->can('dashboard-visit-gate-in');
        $model = new ContainerVisits();
        $model->scenario = ContainerVisits::SCENARIO_GATE_IN;

       

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
                Yii::$app->session->setFlash('error', 'lease fix the errors belowP.');
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
        Yii::$app->user->can('dashboard-visit-gate-out');
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
        // Yii::$app->user->can('dashboard-visit-survey');
        $visit = $this->findModel($visit_id);

        // 1. Find/Create Survey
        $survey = ContainerSurveys::findOne(['visit_id' => $visit_id]);
        if (!$survey) {
            $survey = new ContainerSurveys();
            $survey->visit_id = $visit_id;
            $survey->survey_date = date('Y-m-d H:i:s');
            $survey->surveyor_name = Yii::$app->user->identity->username ?? 'System';
            $survey->approval_status = 'APPROVED';
        }

        // 2. Load Damages
        $damages = $survey->getSurveyDamages()->all();
        if (empty($damages)) {
            $damages = [new SurveyDamages()];
        }

        // --- YARD LOGIC: PREPARE DATA ---
        // Find where it is currently parked
        $currentSlot = YardSlots::findOne(['current_visit_id' => $visit_id]);
        $currentSlotId = $currentSlot ? $currentSlot->slot_id : null;

        // Get list of Empty Slots + The current slot (so it shows in list)
        $slotsQuery = YardSlots::find()->where(['current_visit_id' => null]);
        if ($currentSlotId) {
            $slotsQuery->orWhere(['slot_id' => $currentSlotId]);
        }

        $slotList = ArrayHelper::map(
            $slotsQuery->orderBy(['block' => SORT_ASC, 'row' => SORT_ASC, 'slot_name' => SORT_ASC])->all(),
            'slot_id',
            'slot_name'
        );
        // --------------------------------

        if ($survey->load(Yii::$app->request->post())) {
            if ($visit->load(Yii::$app->request->post())) {
                $visit->save(false);
            }

          

            // Handle Dynamic Model Loading
            $oldDamagesIDs = ArrayHelper::map($damages, 'damage_id', 'damage_id');
            $damages = Model::createMultiple(SurveyDamages::class, $damages);
            Model::loadMultiple($damages, Yii::$app->request->post());

            $valid = $survey->validate();
            $valid = Model::validateMultiple($damages) && $valid;

            if ($valid) {
                  // 2. HANDLE PHOTO
            $photoPath = $survey->uploadSurveyPhoto();
            if ($photoPath) {
                $survey->survey_photo_path = $photoPath;
            }
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    if ($survey->save(false)) {

                        // Save Damages
                        foreach ($damages as $damage) {
                            $damage->survey_id = $survey->survey_id;
                            if (! ($flag = $damage->save(false))) {
                                $transaction->rollBack();
                                break;
                            }
                        }

                        // Delete removed damages
                        if (!empty($oldDamagesIDs)) {
                            $deletedIDs = array_diff($oldDamagesIDs, array_filter(ArrayHelper::map($damages, 'damage_id', 'damage_id')));
                            if (!empty($deletedIDs)) {
                                SurveyDamages::deleteAll(['damage_id' => $deletedIDs]);
                            }
                        }

                        // Update Visit Status
                        if ($visit->status === 'IN_YARD') {
                            $visit->status = 'SURVEYED';
                            $visit->save(false);
                        }

                        // --- YARD LOGIC: SAVE POSITION ---
                        $newSlotId = Yii::$app->request->post('assign_slot_id');

                        // Only update if the slot selection changed
                        if ($newSlotId != $currentSlotId) {
                            // 1. Unpark from old slot (if any)
                            if ($currentSlot) {
                                $currentSlot->current_visit_id = null;
                                $currentSlot->save(false);
                            }
                            // 2. Park in new slot (if selected)
                            if ($newSlotId) {
                                $newSlot = YardSlots::findOne($newSlotId);
                                if ($newSlot) {
                                    $newSlot->current_visit_id = $visit_id;
                                    $newSlot->save(false);
                                }
                            }
                        }
                        // ---------------------------------

                        // Update Bill
                        $bill = BillingRecords::findOne(['visit_id' => $visit->visit_id]);
                        if ($bill) {
                            $bill->recalculateBalance();
                        }

                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'Survey & Yard Position Saved.');
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
            'slotList' => $slotList,       // Pass list to view
            'currentSlotId' => $currentSlotId, // Pass current selection
        ]);
    }
  public function actionGateOut($id)

    {
        // Yii::$app->user->can('dashboard-visit-gate-out');
        $model = $this->findModel($id);

        // --- 1. BILLING CHECK (Keep existing logic) ---
        $bill = \dashboard\models\BillingRecords::findOne(['visit_id' => $id]);

        if (!$bill) {
            $bill = new \dashboard\models\BillingRecords();
            $bill->visit_id = $id;
            $bill->tariff_rate = Yii::$app->config->get('storage_rate_per_day') ?? 0;
            $liftOn = Yii::$app->config->get('lift_on_charges') ?? 0;
            $liftOff = Yii::$app->config->get('lift_off_charges') ?? 0;
            $bill->lift_charges = $liftOn + $liftOff;
        }

        // Calculate Days
        $in = new \DateTime($model->date_in);
        $now = new \DateTime();
        $days = $in->diff($now)->days;
        $bill->storage_days = ($days < 1) ? 1 : $days;

        // Refresh Bill
        $bill->recalculateBalance();

        // Check Payment Status
        $isPaid = ($bill->status === 'PAID' || $bill->status === 'CREDIT' || $bill->balance <= 0.01);

        if (!$isPaid) {
            Yii::$app->session->setFlash('error', 'Container cannot be released. Outstanding Balance: ' . number_format($bill->balance, 2));
            return $this->redirect(['/dashboard/billing/view', 'id' => $bill->bill_id]);
        }

        // --- 2. GATE OUT LOGIC (Fixed File Upload) ---
        $model->scenario = ContainerVisits::SCENARIO_GATE_OUT;

        if (empty($model->date_out)) {
            $model->date_out = date('Y-m-d');
            $model->time_out = date('H:i');
        }

        if ($model->load(Yii::$app->request->post())) {
            
            // 1. Get File Instance
            $model->departure_photo_file = \yii\web\UploadedFile::getInstance($model, 'departure_photo_file');

            // 2. VALIDATE FIRST
            if ($model->validate()) {
                
                // 3. Upload File (Now safe to move)
                $photoPath = $model->uploadDeparturePhoto();
                if ($photoPath) {
                    $model->departure_photo_path = $photoPath;
                }
                
                $model->status = 'GATE_OUT';

                // 4. Save with validation DISABLED (since we already validated)
                if ($model->save(false)) {
                    
                    // Clear Yard Slot
                    $slot = \dashboard\models\YardSlots::findOne(['current_visit_id' => $id]);
                    if ($slot) {
                        $slot->unpark();
                    }

                    Yii::$app->session->setFlash('success', 'Container Released Successfully.');
                    return $this->redirect(['out-index']);
                }
            } else {
                // Show Validation Errors
                $errors = implode('<br>', \yii\helpers\ArrayHelper::getColumn($model->getErrors(), 0));
                Yii::$app->session->setFlash('error', 'Validation Error: ' . $errors);
            }
        }

        return $this->render('gate_out_form', [
            'model' => $model,
        ]);
    }
    public function actionAjaxCreateOwner()

    {
        // Yii::$app->user->can('dashboard-container-owner-create');
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new \dashboard\models\MasterContainerOwners();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return ['success' => true, 'id' => $model->owner_id, 'name' => $model->owner_name];
        }
        return ['success' => false];
    }
    public function actionView($id)

    {
        // Yii::$app->user->can('dashboard-visit-view');
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
