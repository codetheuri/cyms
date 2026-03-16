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

        $data = $this->prepareReportData($request);

        $isExcel = ($request->post('format') === 'excel');

        if ($isExcel) {
            ob_clean();
            header("Content-type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=" . $data['title'] . ".xls");
            return $this->renderPartial('print_custom', array_merge($data, ['isExcel' => true]));
        }

        return $this->renderPartial('print_custom', array_merge($data, ['isExcel' => false]));
    }

    // ... (actionInward, actionOutward) ...
    public function actionInward($id)
    {
        // $this->layout = 'main';
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
    public function actionEmailReport()
    {
        Yii::$app->user->can('dashboard-reports-view');
        $request = Yii::$app->request;
        $emailTo = Yii::$app->config->get('admin_email');
        // $emailTo= "theurij113@gmail.com";
        // $emailTo = $request->post('email_to'); 

        if (empty($emailTo)) {
            Yii::$app->session->setFlash('error', 'Recipient email is required.');
            return $this->redirect(['index']);
        }

        // 1. Get the Data using shared logic
        $data = $this->prepareReportData($request);

        // 2. Generate the Excel Content (Capture output buffer)
        $attachmentContent = $this->renderPartial('print_custom', array_merge($data, ['isExcel' => true]));
        $fileName = $data['title'] . "_" . date('Y-m-d') . ".xls";

        // 3. Initialize Mailer Hook
        $mailer = Yii::createObject(['class' => 'dashboard\hooks\Mail']);

        // 4. Send
        $bodyText = "Hello,\n\nPlease find attached the " . $data['title'] . " generated from the system.";
        $sent = $mailer->sendReportAttachment($emailTo, $data['title'], $bodyText, $attachmentContent, $fileName);

        if ($sent) {
            Yii::$app->session->setFlash('success', 'Report successfully emailed to ' . $emailTo);
        } else {
            Yii::$app->session->setFlash('error', 'Failed to send email. Check SMTP settings.');
        }

        return $this->redirect(['index']);
    }

    public function actionBackupDatabase()
    {
        // 1. Define File Paths
        $dbName = Yii::$app->db->username; // Or parse dsn
        // Note: Better to parse DSN, but for simplicity assuming config is standard
        $dsn = Yii::$app->db->dsn;
        preg_match('/dbname=([^;]*)/', $dsn, $matches);
        $dbName = $matches[1];

        $filename = 'backup_' . $dbName . '_' . date('Y-m-d_H-i-s') . '.sql';
        $zipFilename = $filename . '.zip';
        $savePath = Yii::getAlias('@runtime/') . $filename;
        $zipPath = Yii::getAlias('@runtime/') . $zipFilename;

        // 2. Get DB Credentials
        $username = Yii::$app->db->username;
        $password = Yii::$app->db->password;
        $host = 'localhost'; // Usually localhost

        // 3. Run mysqldump command
        // NOTE: This requires mysqldump to be installed and accessible via shell
        $command = "mysqldump --user={$username} --password={$password} --host={$host} {$dbName} > {$savePath}";
        system($command, $output);

        if (!file_exists($savePath) || filesize($savePath) == 0) {
            Yii::$app->session->setFlash('error', 'Backup failed: Could not generate SQL dump.');
            return $this->redirect(['index']);
        }

        // 4. Zip the file (to save space in email)
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            $zip->addFile($savePath, $filename);
            $zip->close();
        }

        // 5. Send via Email using your existing Hook
        $emailTo = Yii::$app->config->get('admin_email');
        $mailer = Yii::createObject(['class' => 'dashboard\hooks\Mail']);

        // Read the ZIP content
        $attachmentContent = file_get_contents($zipPath);

        $sent = $mailer->sendReportAttachment(
            $emailTo,
            "System Database Backup - " . date('Y-m-d'),
            "Attached is the full system database backup.",
            $attachmentContent,
            $zipFilename
        );

        // 6. Cleanup (Delete temp files)
        @unlink($savePath);
        @unlink($zipPath);

        if ($sent) {
            Yii::$app->session->setFlash('success', 'Database backup emailed successfully!');
        } else {
            Yii::$app->session->setFlash('error', 'Backup generated but email failed.');
        }

        return $this->redirect(['index']);
    }



    protected function prepareReportData($request)
    {
        $type = $request->post('report_type');
        $dateFrom = $request->post('date_from');
        $dateTo = $request->post('date_to');
        $shippingLine = $request->post('shipping_line_id');
        $moveType = $request->post('move_type');

        // Dates for Querying
        $strFrom = $dateFrom;
        $strTo = $dateTo;
        $tsFrom = strtotime($dateFrom . ' 00:00:00');
        $tsTo = strtotime($dateTo . ' 23:59:59');

        $title = "Report";
        $columns = [];
        $query = null;

        // --- HELPER 1: Format Date + Time ---
        $formatDateTime = function ($date, $time) {
            if (!$date) return '-';
            $d = Yii::$app->formatter->asDate($date, 'php:d/m/Y');
            $t = $time ? date('H:i', strtotime($time)) : '00:00';
            return $d . ' ' . $t;
        };

        // --- HELPER 2: Calculate Integer Days ---
        $calcDays = function ($date, $time) {
            if (!$date) return 0;
            $start = strtotime($date . ' ' . ($time ?: '00:00:00'));
            $diff = time() - $start;
            return ($diff < 0) ? 1 : (floor($diff / 86400) + 1);
        };

        // ================= 1. GATE ACTIVITY =================
        if ($type === 'gate_moves') {
            $query = ContainerVisits::find()->joinWith(['containerOwner', 'shippingLine', 'containerType']);
            $query->andFilterWhere(['container_visits.shipping_line_id' => $shippingLine]);

            if ($moveType === 'in') {
                $title = "Gate IN Report ($strFrom to $strTo)";
                $query->andWhere(['between', 'date_in', $strFrom, $strTo]);

                $columns = [
                    ['class' => 'yii\grid\SerialColumn'],
                    'container_number',
                    'containerType.iso_code:text:Type',
                    'shippingLine.line_code:text:Line',
                    [
                        'label' => 'Gate In Time',
                        'value' => function ($m) use ($formatDateTime) {
                            return $formatDateTime($m->date_in, $m->time_in);
                        }
                    ],
                    'seal_number_in:text:Seal No',
                    'vehicle_reg_no_in:text:Truck',
                    'party_delivering_container:text:Party Delivering',
                    [
                        'label' => 'Transporter',
                        'value' => function ($m) {
                            return $m->containerOwner->owner_name ?? $m->truck_owner_name_in;
                        }
                    ]
                ];
            } elseif ($moveType === 'out') {
                $title = "Gate OUT Report ($strFrom to $strTo)";
                $query->andWhere(['between', 'date_out', $strFrom, $strTo])->andWhere(['status' => 'GATE_OUT']);

                $columns = [
                    ['class' => 'yii\grid\SerialColumn'],
                    'container_number',
                    'containerType.iso_code:text:Type',
                    'shippingLine.line_code:text:Line',
                    [
                        'label' => 'Gate Out Time',
                        'value' => function ($m) use ($formatDateTime) {
                            return $formatDateTime($m->date_out, $m->time_out);
                        }
                    ],
                    'seal_number_out:text:Seal No',
                    'vehicle_reg_no_out:text:Truck',
                    'trailer_reg_no_out:text:Trailer',
                    [
                        'label' => 'Gate In Time',
                        'value' => function ($m) use ($formatDateTime) {
                            return $formatDateTime($m->date_in, $m->time_in);
                        }
                    ],
                    'storage_days:text:Days',
                    'destination',
                    'comments_out:text:Remarks',
                ];
            } else {
                $title = "Gate Activity (In & Out) - ($strFrom to $strTo)";
                $query->andWhere(['or', ['between', 'date_in', $strFrom, $strTo], ['between', 'date_out', $strFrom, $strTo]])
                    ->orderBy(['created_at' => SORT_DESC]);

                $columns = [
                    ['class' => 'yii\grid\SerialColumn'],
                    'container_number',
                    'shippingLine.line_code:text:Line',
                    'status',
                    [
                        'label' => 'In',
                        'value' => function ($m) use ($formatDateTime) {
                            return $formatDateTime($m->date_in, $m->time_in);
                        }
                    ],
                    [
                        'label' => 'Out',
                        'value' => function ($m) use ($formatDateTime) {
                            return $formatDateTime($m->date_out, $m->time_out);
                        }
                    ],
                    'storage_days:text:Days',
                    'comments_out:text:Remarks (Out)',
                    ['label' => 'Transporter', 'value' => function ($m) {
                        return $m->containerOwner->owner_name ?? $m->truck_owner_name_in;
                    }]
                ];
            }
        }

        // ================= 2. STOCK LIST =================
        elseif ($type === 'stock_list') {
            $title = "Current Yard Stock List";
            $query = ContainerVisits::find()
                ->where(['status' => ['IN_YARD', 'SURVEYED']])
                ->orderBy(['date_in' => SORT_ASC])
                ->joinWith(['shippingLine', 'containerType']);

            $query->andFilterWhere(['container_visits.shipping_line_id' => $shippingLine]);

            if ($shippingLine) {
                $lineName = MasterShippingLines::findOne($shippingLine)->line_code ?? '';
                $title .= " - " . $lineName;
            }

            $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                'container_number',
                'shippingLine.line_code:text:Line',
                'containerType.iso_code:text:Type',
                [
                    'label' => 'Date In',
                    'value' => function ($m) use ($formatDateTime) {
                        return $formatDateTime($m->date_in, $m->time_in);
                    }
                ],
                'party_delivering_container:text:Delivered By',
                [
                    'label' => 'Days',
                    'contentOptions' => ['style' => 'font-weight:bold; text-align:center;'],
                    'value' => function ($m) use ($calcDays) {
                        return $calcDays($m->date_in, $m->time_in);
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

        // ================= 3. AGING REPORT =================
        elseif ($type === 'aging') {
            $title = "Aging Report (> 30 Days)";
            $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
            $query = ContainerVisits::find()
                ->where(['status' => ['IN_YARD', 'SURVEYED']])
                ->andWhere(['<', 'date_in', $thirtyDaysAgo])
                ->orderBy(['date_in' => SORT_ASC])
                ->joinWith(['shippingLine']);

            $query->andFilterWhere(['container_visits.shipping_line_id' => $shippingLine]);

            $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                'container_number',
                'shippingLine.line_code:text:Line',
                [
                    'label' => 'Date In',
                    'value' => function ($m) use ($formatDateTime) {
                        return $formatDateTime($m->date_in, $m->time_in);
                    }
                ],
                [
                    'label' => 'Days Stayed',
                    'contentOptions' => ['style' => 'color: red; font-weight: bold; text-align:center;'],
                    'value' => function ($m) use ($calcDays) {
                        return $calcDays($m->date_in, $m->time_in);
                    }
                ],
            ];
        }

        // ================= NEW: CREDIT RELEASE REPORT =================
        elseif ($type === 'credit_containers') {
            $title = "Containers Released on Credit ($strFrom to $strTo)";

            // Join tables to get owner details. Filter by 'CREDIT' status and date range on updated_at (Auth Date)
            $query = BillingRecords::find()->joinWith(['visit.containerOwner', 'visit.shippingLine'])
                ->where(['billing_records.status' => 'CREDIT'])
                ->andWhere(['between', BillingRecords::tableName() . '.updated_at', $tsFrom, $tsTo])
                ->orderBy(['updated_at' => SORT_DESC]);

            if ($shippingLine) {
                $query->andWhere(['container_visits.shipping_line_id' => $shippingLine]);
            }

            $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'updated_at',
                    'label' => 'Auth Date',
                    'format' => ['date', 'php:d M Y H:i']
                ],
                'visit.container_number:text:Container',
                'invoice_number',
                [
                    'label' => 'Client / Owner',
                    'value' => function ($m) {
                        return $m->visit->containerOwner->owner_name ?? $m->visit->truck_owner_name_in;
                    }
                ],
                'atl_number:text:ATL No.',
                'authorized_by:text:Approved By',
                [
                    'attribute' => 'grand_total',
                    'label' => 'Amount',
                    'format' => ['currency', 'KES'],
                    'contentOptions' => ['style' => 'text-align: right; font-weight: bold; color: #d35400;']
                ],
            ];
        }

        // ================= FINANCIAL REPORTS =================
        elseif ($type === 'payments') {
            $title = "Payment Collections ($strFrom to $strTo)";
            $query = BillingPayments::find()->joinWith(['bill.visit.containerOwner'])
                ->where(['between', 'transaction_date', $strFrom, $strTo])
                ->orderBy(['transaction_date' => SORT_DESC]);

            $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                'transaction_date:date',
                'bill.visit.container_number:text:Container',
                'method',
                'reference:text:Ref',
                ['attribute' => 'amount', 'format' => ['currency', 'KES'], 'contentOptions' => ['style' => 'text-align: right; font-weight: bold;']],
                'bill.visit.truck_owner_name_in:text:Payer',
            ];
        } elseif ($type === 'invoices') {
            $title = "Invoices Generated ($strFrom to $strTo)";
            $query = BillingRecords::find()->joinWith(['visit'])
                ->where(['between', BillingRecords::tableName() . '.created_at', $tsFrom, $tsTo])
                ->orderBy(['created_at' => SORT_DESC]);

            $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                'invoice_number',
                'visit.container_number',
                'visit.storage_days:text:Days',
                ['attribute' => 'grand_total', 'format' => ['currency', 'KES'], 'contentOptions' => ['style' => 'text-align: right;']],
                ['attribute' => 'balance', 'format' => ['currency', 'KES'], 'contentOptions' => ['style' => 'text-align: right; color: red;']],
                'status'
            ];
        } elseif ($type === 'debtors') {
            $title = "Outstanding Debtors";
            $query = BillingRecords::find()->joinWith(['visit.containerOwner'])
                ->where(['>', 'balance', 0.01])
                ->andWhere(['billing_records.status' => ['UNPAID', 'PARTIAL', 'CREDIT']])
                ->orderBy(['balance' => SORT_DESC]);

            $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                ['label' => 'Client', 'value' => function ($m) {
                    return $m->visit->containerOwner->owner_name ?? $m->visit->truck_owner_name_in;
                }],
                'invoice_number',
                'visit.container_number',
                ['attribute' => 'balance', 'format' => ['currency', 'KES'], 'contentOptions' => ['style' => 'text-align: right; color: red; font-weight: bold;']],
            ];
        } elseif ($type === 'repairs') {
            $title = "Repair Costs Summary";
            $query = BillingRecords::find()->joinWith(['visit'])
                ->where(['>', 'repair_total', 0])
                ->andWhere(['between', BillingRecords::tableName() . '.created_at', $tsFrom, $tsTo]);

            $columns = [
                ['class' => 'yii\grid\SerialColumn'],
                'visit.container_number',
                ['attribute' => 'repair_total', 'format' => ['currency', 'KES'], 'contentOptions' => ['style' => 'text-align: right;']],
                'status',
            ];
        }

        // --- DEFAULT EMPTY QUERY ---
        if (!$query) $query = ContainerVisits::find()->where('0=1');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
            'sort' => false,
        ]);

        return [
            'dataProvider' => $dataProvider,
            'title' => $title,
            'settings' => new General(),
            'columns' => $columns,
            'type' => $type
        ];
    }
    protected function findVisitModel($id)
    {
        if (($model = ContainerVisits::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
