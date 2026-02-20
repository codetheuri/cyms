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
        'dashboard-visit-update' => 'Update Container Visit',
        'dashboard-visit-delete' => 'Delete Container Visit',
    ];

    public function actionIndex()

    {
       Yii::$app->user->can('dashboard-visit-list');
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
        Yii::$app->user->can('dashboard-visit-gate-in');
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
                Yii::$app->session->setFlash('error', 'lease fix the errors below!');
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
        Yii::$app->user->can('dashboard-visit-survey');
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
    Yii::$app->user->can('dashboard-visit-gate-out');
    $model = $this->findModel($id);

    // --- 1. BILLING CHECK ---
    $bill = \dashboard\models\BillingRecords::findOne(['visit_id' => $id]);

    if (!$bill) {
        $bill = new \dashboard\models\BillingRecords();
        $bill->visit_id = $id;
        $bill->tariff_rate = Yii::$app->config->get('storage_rate_per_day') ?? 0;
        $liftOff = Yii::$app->config->get('lift_off_charges') ?? 0;
        $bill->lift_charges = $liftOff;
        $bill->save(false);
    }

    // Recalculate to ensure current status is up to date
    $bill->recalculateBalance();

    // Check Payment Status
    $isPaid = ($bill->status === 'PAID' || $bill->status === 'CREDIT' || $bill->balance <= 0.01);

    if (!$isPaid) {
        Yii::$app->session->setFlash('error', 'Container cannot be released. Outstanding Balance: ' . number_format($bill->balance, 2));
        return $this->redirect(['/dashboard/billing/view', 'id' => $bill->bill_id]);
    }

    // --- 2. GATE OUT LOGIC ---
    $model->scenario = ContainerVisits::SCENARIO_GATE_OUT;

    // Default to NOW, but allow user to change it in form
    if (empty($model->date_out)) {
        $model->date_out = date('Y-m-d');
        $model->time_out = date('H:i');
    }

    if ($model->load(Yii::$app->request->post())) {
        
        // Handle File Upload
        $model->departure_photo_file = \yii\web\UploadedFile::getInstance($model, 'departure_photo_file');

        if ($model->validate()) {
            
            // Upload Photo
            $photoPath = $model->uploadDeparturePhoto();
            if ($photoPath) {
                $model->departure_photo_path = $photoPath;
            }
            
            $model->status = 'GATE_OUT';

            if ($model->save(false)) {
                
                // --- FREEZE BILLING ---
                // Now that we have saved the Date Out (even if backdated),
                // we call recalculateBalance() one last time.
                // The Bill Model will see status='GATE_OUT' and calculate days 
                // based on the specific Date Out we just saved.
                if ($bill) {
                    $bill->recalculateBalance();
                }
                
                // Clear Yard Slot
                $slot = \dashboard\models\YardSlots::findOne(['current_visit_id' => $id]);
                if ($slot) {
                    $slot->unpark();
                }

                Yii::$app->session->setFlash('success', 'Container Released Successfully.');
                return $this->redirect(['out-index']);
            }
        } else {
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
        Yii::$app->user->can('dashboard-container-owner-create');
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
 

    public function actionUpdate($id)
{
    Yii::$app->user->can('dashboard-visit-view');
    $model = $this->findModel($id);
    
    // Use Gate In scenario to validate fields like container number format
    $model->scenario = ContainerVisits::SCENARIO_GATE_IN;

    if ($model->load(Yii::$app->request->post())) {
        
        // Handle Photo Update if they upload a new one
        $model->arrival_photo_file = UploadedFile::getInstance($model, 'arrival_photo_file');
        if ($model->arrival_photo_file) {
            $path = $model->uploadArrivalPhoto();
            if ($path) {
                $model->arrival_photo_path = $path;
            }
        }

        if ($model->save()) {
            // CRITICAL: If dates changed, recalculate the Bill
            $bill = BillingRecords::findOne(['visit_id' => $model->visit_id]);
            if ($bill) {
                $bill->recalculateBalance(); 
            }

            Yii::$app->session->setFlash('success', 'Visit details updated successfully.');
            return $this->redirect(['view', 'id' => $model->visit_id]);
        }
    }

    // Prepare Dropdowns (Required for gate_in_form to work)
    $shippingLines = ArrayHelper::map(MasterShippingLines::find()->all(), 'line_id', 'line_name');
    $owners = ArrayHelper::map(MasterContainerOwners::find()->all(), 'owner_id', 'owner_name');
    $types = ArrayHelper::map(MasterContainerTypes::find()->all(), 'type_id', function ($m) {
        return $m->size . "' " . $m->type_group . ' (' . $m->iso_code . ')';
    });

    // --- THE FIX IS HERE ---
    // Point to 'gate_in_form' instead of 'update'
    return $this->render('gate_in_form', [
        'model' => $model,
        'shippingLines' => $shippingLines, // Must pass these!
        'owners' => $owners,
        'types' => $types,
    ]);
}

    /**
     * ACTION: Soft Delete / Restore (Toggle)
     */
    public function actionTrash($id)
    {
        Yii::$app->user->can('dashboard-visit-view');
        $model = $this->findModel($id);
        
        if ($model->is_deleted) {
            // Restore
            $model->restore(); // Assuming your BaseModel has restore() logic handling is_deleted=0
            Yii::$app->session->setFlash('success', 'Record has been restored.');
        } else {
            // Soft Delete
            $model->delete(); // Assuming your BaseModel treats delete() as soft delete if softDelete behavior is attached
            // OR if you do it manually:
            // $model->is_deleted = 1; $model->save(false);
            
            Yii::$app->session->setFlash('warning', 'Record moved to trash.');
        }
        
        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    /**
     * ACTION: Permanent Delete
     * This physically removes the record from the database.
     */
    public function actionForceDelete($id)
    {
        Yii::$app->user->can('dashboard-visit-view');
        $model = $this->findModel($id);
        
        try {
            // Force Delete logic (Physically remove row)
            // If your BaseModel uses SoftDeleteBehavior, you might need $model->forceDelete();
            // If not using behavior, standard $model->delete() on a soft-deleted record typically still just updates flag.
            // To physically delete in Yii2 manually:
             $model->forceDelete();
            
            Yii::$app->session->setFlash('success', 'Record permanently deleted.');
            
        } catch (\Exception $e) {
            // Check for Foreign Key Constraints
            if ($e->getCode() == 23000 || strpos($e->getMessage(), '1451') !== false) {
                Yii::$app->session->setFlash('error', '<b>Cannot Delete:</b> This visit has related records (e.g., Billing, Surveys) that prevent deletion.');
            } else {
                Yii::$app->session->setFlash('error', 'Delete Failed: ' . $e->getMessage());
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * ACTION: Render Restore/Delete Options Modal
     */
    public function actionRestoreOption($id)
    {
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_restore_option', [
                'model' => $this->findModel($id)
            ]);
        }
        return $this->redirect(['index']);
    }
    public function actionAjaxComment($id)
    {
        $model = $this->findModel($id);
        
        // If form submitted
        if ($model->load(Yii::$app->request->post())) {
            // Save only the comments field (skip other validations for speed)
            // We use updateAttributes to be precise and safe
            $model->updateAttributes(['comments_in' => $model->comments_in]);
            
            Yii::$app->session->setFlash('success', 'Flags/Comments updated.');
            return $this->redirect(['index']);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_comment_form', [
                'model' => $model
            ]);
        }
        return $this->redirect(['index']);
    }
    protected function findModel($id)
    {
        if (($model = ContainerVisits::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
