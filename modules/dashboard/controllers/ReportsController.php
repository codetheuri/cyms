<?php

namespace dashboard\controllers;

use Yii;
use dashboard\models\ContainerVisits;
use dashboard\models\BillingRecords;
use dashboard\models\BillingPayments;
use dashboard\models\ContainerSurveys;
use admin\models\static\General;
use helpers\DashboardController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;

class ReportsController extends DashboardController
{
    // Standard Layout for Index, None for Print
    // public function init()
    // {
    //     parent::init();
    //     if (in_array($this->action->id, ['inward', 'outward', 'generate'])) {
    //         $this->layout = false;
    //     } else {
    //         $this->layout = 'dashboard';
    //     }
    // }

    public function getViewPath()
    {
        return Yii::getAlias('@ui/views/cyms/reports');
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * THE MASTER REPORT GENERATOR
     * Handles PDF (Screen) and Excel Logic for 6 Report Types
     */
    public function actionGenerate()
    {
        $request = Yii::$app->request;
        $type = $request->post('report_type');
        $format = $request->post('format'); // 'print' or 'excel'
        $dateFrom = $request->post('date_from');
        $dateTo = $request->post('date_to');

        // --- 1. PREPARE DATES (Fixes Blank Reports) ---
        // Strings for DATE columns (e.g., '2023-01-01')
        $strFrom = $dateFrom;
        $strTo = $dateTo;
        // Integers for TIMESTAMP columns (e.g., 1672531200)
        $tsFrom = strtotime($dateFrom . ' 00:00:00');
        $tsTo = strtotime($dateTo . ' 23:59:59');

        $title = "Report";
        $columns = [];
        $query = null;

        // ================= REPORT TYPES =================

        // 1. GATE MOVES (Operational)
        if ($type === 'gate_moves') {
            $title = "Gate Activity ($strFrom to $strTo)";
            $query = ContainerVisits::find()
                ->where(['between', 'date_in', $strFrom, $strTo])
                ->orWhere(['between', 'date_out', $strFrom, $strTo])
                ->orderBy(['created_at' => SORT_DESC])
                ->joinWith(['containerOwner']);

            $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                'container_number',
                'status',
                'date_in:date',
                'time_in',
                'date_out:date',
                [
                    'label' => 'Transporter/Owner',
                    'value' => function($m) { return $m->containerOwner->owner_name ?? $m->truck_owner_name_in; }
                ]
            ];
        }

        // 2. STOCK LIST (Inventory)
        elseif ($type === 'stock_list') {
            $title = "Current Yard Stock List";
            $query = ContainerVisits::find()
                ->where(['status' => ['IN_YARD', 'SURVEYED']])
                ->orderBy(['date_in' => SORT_ASC]) // FIFO
                ->joinWith(['shippingLine']);

            $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                'container_number',
                'shippingLine.line_code:text:Line',
                'date_in:date',
                [
                    'label' => 'Days in Yard',
                    'value' => function ($m) {
                        return (new \DateTime($m->date_in))->diff(new \DateTime())->days . ' days';
                    }
                ],
                'status',
                [
                    'label' => 'Condition',
                    'value' => function ($m) {
                        return $m->getContainerSurvey()->exists() ? $m->containerSurvey->approval_status : 'Pending Survey';
                    }
                ]
            ];
        }

        // 3. AGING REPORT (Risk)
        elseif ($type === 'aging') {
            $title = "Aging Report (> 30 Days)";
            $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
            $query = ContainerVisits::find()
                ->where(['status' => ['IN_YARD', 'SURVEYED']])
                ->andWhere(['<', 'date_in', $thirtyDaysAgo])
                ->orderBy(['date_in' => SORT_ASC]);

            $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                'container_number',
                'date_in:date',
                [
                    'label' => 'Days Stayed',
                    'contentOptions' => ['style' => 'color: red; font-weight: bold;'],
                    'value' => function ($m) {
                        return (new \DateTime($m->date_in))->diff(new \DateTime())->days;
                    }
                ],
                [
                    'label' => 'Owner',
                    'value' => function($m) { return $m->containerOwner->owner_name ?? $m->truck_owner_name_in; }
                ]
            ];
        }

        // 4. MONEY PAID (Financial - Cash Book)
        elseif ($type === 'payments') {
            $title = "Payment Collections ($strFrom to $strTo)";
            $query = BillingPayments::find()
                ->joinWith(['bill.visit.containerOwner'])
                ->where(['between', 'transaction_date', $strFrom, $strTo])
                ->orderBy(['transaction_date' => SORT_DESC]);

            $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                'transaction_date:date',
                'bill.visit.container_number:text:Container',
                'method',
                'reference:text:Ref No.',
                [
                    'attribute' => 'amount',
                    'format' => ['currency', 'KES'],
                    'contentOptions' => ['style' => 'text-align: right; font-weight: bold;'],
                ],
                [
                    'label' => 'Payer',
                    'value' => function($m) { return $m->bill->visit->containerOwner->owner_name ?? $m->bill->visit->truck_owner_name_in; }
                ]
            ];
        }

        // 5. INVOICES RAISED (Financial - Revenue)
        elseif ($type === 'invoices') {
            $title = "Invoices Generated ($strFrom to $strTo)";
            // Use INT Timestamp for created_at
            $query = BillingRecords::find()
                ->joinWith(['visit'])
                ->where(['between', BillingRecords::tableName() . '.created_at', $tsFrom, $tsTo])
                ->orderBy(['created_at' => SORT_DESC]);

            $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                'invoice_number',
                'visit.container_number',
                [
                    'attribute' => 'grand_total',
                    'format' => ['currency', 'KES'],
                    'contentOptions' => ['style' => 'text-align: right;'],
                ],
                [
                    'attribute' => 'balance',
                    'format' => ['currency', 'KES'],
                    'contentOptions' => ['style' => 'text-align: right; color: red;'],
                ],
                'status'
            ];
        }

        // 6. DEBTORS LIST (Outstanding)
        elseif ($type === 'debtors') {
            $title = "Outstanding Debtors (Unpaid Invoices)";
            $query = BillingRecords::find()
                ->joinWith(['visit.containerOwner'])
                ->where(['>', 'balance', 0])
                ->andWhere(['billing_records.status' => ['UNPAID', 'PARTIAL', 'CREDIT']])
                ->orderBy(['balance' => SORT_DESC]);

            $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'label' => 'Client / Owner',
                    'value' => function($m) { return $m->visit->containerOwner->owner_name ?? $m->visit->truck_owner_name_in; }
                ],
                'invoice_number',
                'visit.container_number',
                [
                    'attribute' => 'balance',
                    'format' => ['currency', 'KES'],
                    'contentOptions' => ['style' => 'text-align: right; color: red; font-weight: bold;'],
                ],
                'status'
            ];
        }

        // --- EXPORT HANDLER ---
        $dataProvider = new ActiveDataProvider([
            'query' => $query ?: ContainerVisits::find()->where('0=1'),
            'pagination' => false, // Dump all data
            'sort' => false,
        ]);

        $settings = new General();
        $isExcel = ($format === 'excel');

        if ($isExcel) {
            ob_clean(); // Clear buffer
            header("Content-type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=Report_" . $type . "_" . date('Ymd') . ".xls");
            return $this->renderPartial('print_custom', [
                'dataProvider' => $dataProvider,
                'title' => $title,
                'settings' => $settings,
                'columns' => $columns,
                'isExcel' => true
            ]);
        }

        return $this->renderPartial('print_custom', [
            'dataProvider' => $dataProvider,
            'title' => $title,
            'settings' => $settings,
            'columns' => $columns,
            'isExcel' => false
        ]);
    }

    // ... (Keep your actionInward and actionOutward here unchanged) ...
    
    public function actionInward($id)
    {
        $visit = $this->findVisitModel($id);
        $survey = ContainerSurveys::findOne(['visit_id' => $id]);
        $settings = new General(); 
        return $this->render('inward_interchange', ['visit' => $visit, 'survey' => $survey, 'settings' => $settings]);
    }

    public function actionOutward($id)
    {
        $visit = $this->findVisitModel($id);
        $settings = new General(); 
        return $this->render('outward_interchange', ['visit' => $visit, 'settings' => $settings]);
    }

    protected function findVisitModel($id)
    {
        if (($model = ContainerVisits::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}