<?php

namespace dashboard\controllers;

use Yii;
use dashboard\models\ContainerVisits;
use dashboard\models\BillingRecords;
use dashboard\models\BillingPayments;
use dashboard\models\ContainerSurveys;
use helpers\DashboardController;
use yii\helpers\ArrayHelper;
use yii\db\Query;

class DefaultController extends DashboardController
{
    public $layout = 'dashboard';

    public function getViewPath()
    {
        return Yii::getAlias('@ui/views/cyms/default');
    }

    public function actionIndex()
    {
        // --- 1. HEADLINE KPI COUNTERS ---
        $today = date('Y-m-d');
        
        // Yard Utilization
        $totalInYard = ContainerVisits::find()
            ->where(['status' => ['IN_YARD', 'SURVEYED']])
            ->count();

        // Daily Activity
        $gateInToday = ContainerVisits::find()->where(['date_in' => $today])->count();
        $gateOutToday = ContainerVisits::find()->where(['date_out' => $today, 'status' => 'GATE_OUT'])->count();
        
        // Financial Pulse (Monthly)
        $revenueMonth = BillingPayments::find()
            ->where(['between', 'transaction_date', date('Y-m-01'), date('Y-m-t')])
            ->sum('amount');

        // Pending Work
        $pendingSurveys = ContainerVisits::find()
            ->where(['status' => 'IN_YARD']) // Not yet surveyed
            ->count();


        // --- 2. CHART DATA: GATE ACTIVITY (LAST 7 DAYS) ---
        $chartLabels = [];
        $chartDataIn = [];
        $chartDataOut = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chartLabels[] = date('d M', strtotime($date));
            
            $chartDataIn[] = (int) ContainerVisits::find()->where(['date_in' => $date])->count();
            $chartDataOut[] = (int) ContainerVisits::find()->where(['date_out' => $date, 'status' => 'GATE_OUT'])->count();
        }


        // --- 3. CHART DATA: STOCK BY SHIPPING LINE ---
        // Top 5 Shipping Lines in Yard
        $stockDistribution = ContainerVisits::find()
            ->select(['shipping_line_id', 'COUNT(*) as count'])
            ->where(['status' => ['IN_YARD', 'SURVEYED']])
            ->groupBy('shipping_line_id')
            ->orderBy(['count' => SORT_DESC])
            ->limit(5)
            ->with('shippingLine')
            ->all();

        // $pieLabels = [];
        // $pieData = [];

        // foreach ($stockDistribution as $item) {
        //     $name = $item->shippingLine ? $item->shippingLine->line_code : 'Unknown';
        //     $pieLabels[] = $name;
        //     $pieData[] = $item->count; // Virtual attribute logic might be needed, simplified here
        // }
        $rawStock = (new Query())
            ->select(['s.line_code', 'COUNT(v.visit_id) as total'])
            ->from(['v' => ContainerVisits::tableName()])
            ->leftJoin(['s' => 'master_shipping_lines'], 's.line_id = v.shipping_line_id')
            ->where(['v.status' => ['IN_YARD', 'SURVEYED']])
            ->groupBy(['s.line_code'])
            ->orderBy(['total' => SORT_DESC])
            ->limit(5)
            ->all();
            
        // Extract columns simply
        $pieLabels = ArrayHelper::getColumn($rawStock, 'line_code');
        $pieData = ArrayHelper::getColumn($rawStock, 'total');
        
        // Fallback if data is empty to prevent chart crash
        if (empty($pieLabels)) {
            $pieLabels = ['No Data'];
            $pieData = [0];
        }
        // Fallback if array key access issues arise with direct query:
        // Using raw SQL is often safer for aggregation in widgets, but ActiveRecord is cleaner.
        // *Fix logic*: $item->count is not a property of ActiveRecord unless declared.
        // Alternative: Use ArrayHelper map on a raw query.
        $rawStock = (new \yii\db\Query())
            ->select(['l.line_code', 'COUNT(v.visit_id) as total'])
            ->from('container_visits v')
            ->leftJoin('master_shipping_lines l', 'l.line_id = v.shipping_line_id')
            ->where(['v.status' => ['IN_YARD', 'SURVEYED']])
            ->groupBy('l.line_code')
            ->orderBy(['total' => SORT_DESC])
            ->limit(5)
            ->all();
            
        $pieLabels = ArrayHelper::getColumn($rawStock, 'line_code');
        $pieData = ArrayHelper::getColumn($rawStock, 'total');


        // --- 4. RECENT LISTS ---
        $recentIn = ContainerVisits::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        $recentPaid = BillingRecords::find()
            ->where(['status' => 'PAID'])
            ->orderBy(['updated_at' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('index', [
            'totalInYard' => $totalInYard,
            'gateInToday' => $gateInToday,
            'gateOutToday' => $gateOutToday,
            'revenueMonth' => $revenueMonth,
            'pendingSurveys' => $pendingSurveys,
            'chartLabels' => $chartLabels,
            'chartDataIn' => $chartDataIn,
            'chartDataOut' => $chartDataOut,
            'pieLabels' => $pieLabels,
            'pieData' => $pieData,
            'recentIn' => $recentIn,
            'recentPaid' => $recentPaid,
        ]);
    }
}