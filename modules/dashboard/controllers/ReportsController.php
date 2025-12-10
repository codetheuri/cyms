<?php

namespace dashboard\controllers;

use Yii;
use dashboard\models\ContainerVisits;
use dashboard\models\BillingRecords;
use dashboard\models\BillingPayments;
use dashboard\models\ContainerSurveys;
use dashboard\models\MasterShippingLines; // Needed for dropdown check
use admin\models\static\General;
use helpers\DashboardController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use yii\db\Expression;

class ReportsController extends DashboardController
{
    public $permissions = [
        'dashboard-reports-view' => 'View Reports',
    ];

    public function getViewPath()
    {
        return Yii::getAlias('@ui/views/cyms/reports');
    }

    public function actionIndex()
    {
        Yii::$app->user->can('dashboard-reports-view');
        return $this->render('index');
    }

    public function actionGenerate()
    {
        Yii::$app->user->can('dashboard-reports-view');
        $request = Yii::$app->request;
        
        // --- INPUTS ---
        $type = $request->post('report_type');
        $format = $request->post('format');
        $dateFrom = $request->post('date_from');
        $dateTo = $request->post('date_to');
        
        // New Filters
        $shippingLine = $request->post('shipping_line_id'); // Filter by Line
        $moveType = $request->post('move_type'); // Filter Gate Moves (in, out, all)

        // --- DATES ---
        $strFrom = $dateFrom;
        $strTo = $dateTo;
        $tsFrom = strtotime($dateFrom . ' 00:00:00');
        $tsTo = strtotime($dateTo . ' 23:59:59');

        $title = "Report";
        $columns = [];
        $query = null;

        // ================= 1. GATE ACTIVITY (With Direction Filter) =================
        if ($type === 'gate_moves') {
            
            $query = ContainerVisits::find()->joinWith(['containerOwner', 'shippingLine']);

            // Filter by Shipping Line
            $query->andFilterWhere(['shipping_line_id' => $shippingLine]);

            // Filter by Direction
            if ($moveType === 'in') {
                $title = "Gate IN Report ($strFrom to $strTo)";
                $query->andWhere(['between', 'date_in', $strFrom, $strTo]);
                
                // Hide Gate Out columns if only viewing IN
                $columns = [
                    ['class' => 'yii\grid\SerialColumn'],
                    'container_number',
                    'shippingLine.line_code:text:Line',
                    'date_in:date',
                    'time_in',
                    'vehicle_reg_no_in:text:Truck',
                    [
                        'label' => 'Transporter',
                        'value' => function($m) { return $m->containerOwner->owner_name ?? $m->truck_owner_name_in; }
                    ]
                ];

            } elseif ($moveType === 'out') {
                $title = "Gate OUT Report ($strFrom to $strTo)";
                $query->andWhere(['between', 'date_out', $strFrom, $strTo])
                      ->andWhere(['status' => 'GATE_OUT']);
                
                $columns = [
                    ['class' => 'yii\grid\SerialColumn'],
                    'container_number',
                    'shippingLine.line_code:text:Line',
                    'date_out:date',
                    'time_out',
                    'vehicle_reg_no_out:text:Truck',
                    'destination',
                ];

            } else {
                // ALL MOVES
                $title = "Gate Activity (In & Out) - ($strFrom to $strTo)";
                $query->andWhere(['or', 
                    ['between', 'date_in', $strFrom, $strTo],
                    ['between', 'date_out', $strFrom, $strTo]
                ])->orderBy(['created_at' => SORT_DESC]);

                $columns = [
                    ['class' => 'yii\grid\SerialColumn'],
                    'container_number',
                    'shippingLine.line_code:text:Line',
                    'status',
                    'date_in:date',
                    'date_out:date',
                    [
                        'label' => 'Transporter',
                        'value' => function($m) { return $m->containerOwner->owner_name ?? $m->truck_owner_name_in; }
                    ]
                ];
            }
        }

        // ================= 2. STOCK LIST (With Line Filter) =================
        elseif ($type === 'stock_list') {
            $title = "Current Yard Stock List";
            $query = ContainerVisits::find()
                ->where(['status' => ['IN_YARD', 'SURVEYED']])
                ->orderBy(['date_in' => SORT_ASC])
                ->joinWith(['shippingLine']);
            
            // Apply Line Filter
            $query->andFilterWhere(['shipping_line_id' => $shippingLine]);

            if ($shippingLine) {
                $lineName = MasterShippingLines::findOne($shippingLine)->line_code ?? '';
                $title .= " - " . $lineName;
            }

            $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                'container_number',
                'shippingLine.line_code:text:Line',
                'containerType.iso_code:text:Type',
                'date_in:date',
                [
                    'label' => 'Days',
                    'value' => function ($m) {
                        return (new \DateTime($m->date_in))->diff(new \DateTime())->days;
                    }
                ],
                'status',
                [
                    'label' => 'Condition',
                    'value' => function ($m) {
                        return $m->getContainerSurvey()->exists() ? $m->containerSurvey->approval_status : 'Pending';
                    }
                ]
            ];
        }

        // ================= 3. AGING REPORT (With Line Filter) =================
        elseif ($type === 'aging') {
            $title = "Aging Report (> 30 Days)";
            $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
            $query = ContainerVisits::find()
                ->where(['status' => ['IN_YARD', 'SURVEYED']])
                ->andWhere(['<', 'date_in', $thirtyDaysAgo])
                ->orderBy(['date_in' => SORT_ASC])
                ->joinWith(['shippingLine']);
            
            $query->andFilterWhere(['shipping_line_id' => $shippingLine]);

            $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                'container_number',
                'shippingLine.line_code:text:Line',
                'date_in:date',
                [
                    'label' => 'Days Stayed',
                    'contentOptions' => ['style' => 'color: red; font-weight: bold;'],
                    'value' => function ($m) {
                        return (new \DateTime($m->date_in))->diff(new \DateTime())->days;
                    }
                ],
            ];
        }

        // ================= FINANCIAL REPORTS (No Change needed, but included for completeness) =================
        
        // 4. PAYMENTS
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
                'reference:text:Ref',
                [
                    'attribute' => 'amount',
                    'format' => ['currency', 'KES'],
                    'contentOptions' => ['style' => 'text-align: right; font-weight: bold;'],
                ],
                'bill.visit.truck_owner_name_in:text:Payer',
            ];
        }

        // 5. INVOICES
        elseif ($type === 'invoices') {
            $title = "Invoices Generated ($strFrom to $strTo)";
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
        
        // 6. DEBTORS
        elseif ($type === 'debtors') {
            $title = "Outstanding Debtors";
            $query = BillingRecords::find()
                ->joinWith(['visit.containerOwner'])
                ->where(['>', 'balance', 0.01])
                ->andWhere(['billing_records.status' => ['UNPAID', 'PARTIAL', 'CREDIT']])
                ->orderBy(['balance' => SORT_DESC]);

             $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'label' => 'Client',
                    'value' => function($m) { return $m->visit->containerOwner->owner_name ?? $m->visit->truck_owner_name_in; }
                ],
                'invoice_number',
                'visit.container_number',
                [
                    'attribute' => 'balance',
                    'format' => ['currency', 'KES'],
                    'contentOptions' => ['style' => 'text-align: right; color: red; font-weight: bold;'],
                ],
            ];
        }
        
        // 7. REPAIRS
        elseif ($type === 'repairs') {
            $title = "Repair Costs Summary";
            $query = BillingRecords::find()
                ->joinWith(['visit'])
                ->where(['>', 'repair_total', 0])
                ->andWhere(['between', BillingRecords::tableName() . '.created_at', $tsFrom, $tsTo]);

            $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                'visit.container_number',
                [
                    'attribute' => 'repair_total',
                    'format' => ['currency', 'KES'],
                    'contentOptions' => ['style' => 'text-align: right;'],
                ],
                'status',
            ];
        }

        // --- OUTPUT ---
        if (!$query) $query = ContainerVisits::find()->where('0=1');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
            'sort' => false,
        ]);

        $settings = new General();
        $isExcel = ($format === 'excel');

        if ($isExcel) {
            ob_clean();
            header("Content-type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=Report_" . $type . "_" . date('Ymd') . ".xls");
            return $this->renderPartial('print_custom', [
                'dataProvider' => $dataProvider, 'title' => $title, 'settings' => $settings, 'columns' => $columns, 'isExcel' => true
            ]);
        }

        return $this->renderPartial('print_custom', [
            'dataProvider' => $dataProvider, 'title' => $title, 'settings' => $settings, 'columns' => $columns, 'isExcel' => false
        ]);
    }
    
    // ... (actionInward, actionOutward) ...
     public function actionInward($id)
    {
        Yii::$app->user->can('dashboard-container-owner-view');
        $visit = $this->findVisitModel($id);
        $survey = ContainerSurveys::findOne(['visit_id' => $id]);
        $settings = new General(); 
        return $this->render('inward_interchange', ['visit' => $visit, 'survey' => $survey, 'settings' => $settings]);
    }

    public function actionOutward($id)
    {
        Yii::$app->user->can('dashboard-container-owner-view');
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