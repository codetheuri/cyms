<?php

namespace dashboard\controllers;

use Yii;
use dashboard\models\ContainerVisits;
use dashboard\models\BillingRecords;
use dashboard\models\BillingPayments;
use dashboard\models\ContainerSurveys;
use helpers\DashboardController;
use helpers\PermissionHelper;
use yii\helpers\ArrayHelper;
use yii\db\Query;

class DefaultController extends DashboardController
{
    public $layout = 'dashboard';
    public $permissions = [
        'dashboard-default-view' => 'View Dashboard',
    ];
    
    public function getViewPath()
    {
        return Yii::getAlias('@ui/views/cyms/default');
    }
    
    /**
     * Override beforeAction to handle permission checking differently
     * for the dashboard index action
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        
        // For the index action, we handle permissions differently
        // Don't throw 403, just let the action run
        if ($action->id === 'index') {
            return true;
        }
        
        return true;
    }

    public function actionIndex()
    {
        // Check permission but handle it gracefully
        $hasDashboardAccess = PermissionHelper::can('dashboard-default-view');
        
        $today = date('Y-m-d');
        $data = [];
        $charts = [];
        $recentLists = [];
        
        // Only fetch data if user has permission to see specific widgets
        if ($hasDashboardAccess) {
            // Yard Utilization
            if (PermissionHelper::can('dashboard-yard-list')) {
                $data['totalInYard'] = ContainerVisits::find()
                    ->where(['status' => ['IN_YARD', 'SURVEYED']])
                    ->count();
            }
            
            // Daily Activity
            if (PermissionHelper::canAny(['dashboard-visit-gate-in', 'dashboard-visit-gate-out'])) {
                if (PermissionHelper::can('dashboard-visit-gate-in')) {
                    $data['gateInToday'] = ContainerVisits::find()->where(['date_in' => $today])->count();
                }
                
                if (PermissionHelper::can('dashboard-visit-gate-out')) {
                    $data['gateOutToday'] = ContainerVisits::find()->where(['date_out' => $today, 'status' => 'GATE_OUT'])->count();
                }
            }
            
            // Financial Pulse
            if (PermissionHelper::can('dashboard-billing-list')) {
                $data['revenueMonth'] = BillingPayments::find()
                    ->where(['between', 'transaction_date', date('Y-m-01'), date('Y-m-t')])
                    ->sum('amount') ?? 0;
            }
            
            // Pending Surveys
            if (PermissionHelper::can('dashboard-survey-list')) {
                $data['pendingSurveys'] = ContainerVisits::find()
                    ->where(['status' => 'IN_YARD'])
                    ->count();
            }
            
            // --- CHART DATA: GATE ACTIVITY (LAST 7 DAYS) ---
            // Only show chart if user can view gate operations
            if (PermissionHelper::canAny(['dashboard-visit-gate-in', 'dashboard-visit-gate-out'])) {
                $chartLabels = [];
                $chartDataIn = [];
                $chartDataOut = [];

                for ($i = 6; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    $chartLabels[] = date('d M', strtotime($date));
                    
                    if (PermissionHelper::can('dashboard-visit-gate-in')) {
                        $chartDataIn[] = (int) ContainerVisits::find()->where(['date_in' => $date])->count();
                    } else {
                        $chartDataIn[] = 0;
                    }
                    
                    if (PermissionHelper::can('dashboard-visit-gate-out')) {
                        $chartDataOut[] = (int) ContainerVisits::find()->where(['date_out' => $date, 'status' => 'GATE_OUT'])->count();
                    } else {
                        $chartDataOut[] = 0;
                    }
                }
                
                $charts['gateActivity'] = [
                    'labels' => $chartLabels,
                    'dataIn' => $chartDataIn,
                    'dataOut' => $chartDataOut,
                ];
            }
            
            // --- CHART DATA: STOCK BY SHIPPING LINE ---
            // Only show if user can view yard
            if (PermissionHelper::can('dashboard-yard-list')) {
                $rawStock = (new Query())
                    ->select(['s.line_code', 'COUNT(v.visit_id) as total'])
                    ->from(['v' => ContainerVisits::tableName()])
                    ->leftJoin(['s' => 'master_shipping_lines'], 's.line_id = v.shipping_line_id')
                    ->where(['v.status' => ['IN_YARD', 'SURVEYED']])
                    ->groupBy(['s.line_code'])
                    ->orderBy(['total' => SORT_DESC])
                    ->limit(5)
                    ->all();
                    
                $pieLabels = ArrayHelper::getColumn($rawStock, 'line_code');
                $pieData = ArrayHelper::getColumn($rawStock, 'total');
                
                // Fallback if data is empty to prevent chart crash
                if (empty($pieLabels)) {
                    $pieLabels = ['No Data'];
                    $pieData = [0];
                }
                
                $charts['stockByLine'] = [
                    'labels' => $pieLabels,
                    'data' => $pieData,
                ];
            }
            
            // --- RECENT LISTS ---
            // Recent Arrivals - only if user can view gate-in
            if (PermissionHelper::can('dashboard-visit-gate-in')) {
                $recentLists['arrivals'] = ContainerVisits::find()
                    ->orderBy(['created_at' => SORT_DESC])
                    ->limit(5)
                    ->all();
            }
            
            // Recent Paid Invoices - only if user can view billing
            if (PermissionHelper::can('dashboard-billing-list')) {
                $recentLists['paidInvoices'] = BillingRecords::find()
                    ->where(['status' => 'PAID'])
                    ->orderBy(['updated_at' => SORT_DESC])
                    ->limit(5)
                    ->all();
            }
        }
        
        // Check if user has any accessible modules
        $accessibleModules = $this->getAccessibleModules();
        
        return $this->render('index', [
            'hasDashboardAccess' => $hasDashboardAccess,
            'data' => $data,
            'accessibleModules' => $accessibleModules,
            'charts' => $charts,
            'recentLists' => $recentLists,
            'user' => Yii::$app->user,
        ]);
    }

    private function getAccessibleModules()
    {
        $modules = [];
        
        $modulePermissions = [
            'Gate Operations' => [
                'dashboard-visit-gate-in' => '/dashboard/visit/index',
                'dashboard-visit-gate-out' => '/dashboard/visit/out-index',
            ],
            'Container Management' => [
                'dashboard-survey-list' => '/dashboard/survey/index',
                'dashboard-yard-list' => '/dashboard/yard/index',
            ],
            'Financial' => [
                'dashboard-billing-list' => '/dashboard/billing/index',
            ],
            'Clients' => [
                'dashboard-client-list' => '/dashboard/container-owner/index',
            ],
            'Reports' => [
                'dashboard-report-list' => '/dashboard/reports/index',
            ],
            'Administration' => [
                'dashboard-profile-list' => '/profile/index',
            ],
        ];
        
        foreach ($modulePermissions as $category => $perms) {
            $accessibleItems = [];
            foreach ($perms as $permission => $route) {
                if (PermissionHelper::can($permission)) {
                    $accessibleItems[] = [
                        'label' => $this->getLabelFromRoute($route),
                        'url' => [$route],
                        'permission' => $permission,
                    ];
                }
            }
            
            if (!empty($accessibleItems)) {
                $modules[$category] = $accessibleItems;
            }
        }
        
        return $modules;
    }

    private function getLabelFromRoute($route)
    {
        $labels = [
            '/dashboard/visit/index' => 'Gate IN',
            '/dashboard/visit/out-index' => 'Gate OUT',
            '/dashboard/survey/index' => 'Container Survey',
            '/dashboard/yard/index' => 'Yard Position',
            '/dashboard/billing/index' => 'Billing',
            '/dashboard/container-owner/index' => 'Clients / Owners',
            '/dashboard/reports/index' => 'Reports',
            '/profile/index' => 'User Management',
            '/role/index' => 'Manage Roles',
        ];
        
        return $labels[$route] ?? 'Module';
    }
}