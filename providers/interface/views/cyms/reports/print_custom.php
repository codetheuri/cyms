<?php
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $settings admin\models\static\General */
/* @var $title string */
/* @var $columns array */
/* @var $isExcel bool */

$logoUrl = null;
if (!$isExcel && $settings->site_logo) {
    $logoUrl = Yii::getAlias('@web') . '/' . $settings->site_logo;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= Html::encode($title) ?></title>
    <?php if (!$isExcel): ?>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .report-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .report-table th { background: #eee; border: 1px solid #999; padding: 6px; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .report-table td { border: 1px solid #999; padding: 6px; }
        .no-print { text-align: right; margin-bottom: 10px; }
        @media print { .no-print { display: none; } }
    </style>
    <?php endif; ?>
</head>
<body>

    <?php if (!$isExcel): ?>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 8px 15px; background: #333; color: #fff; border:none; cursor:pointer;">Print Report</button>
    </div>
    <?php endif; ?>

    <div class="header">
        <?php if ($logoUrl): ?>
            <img src="<?= $logoUrl ?>" style="height: 60px;">
        <?php endif; ?>
        <h2 style="margin: 5px 0;"><?= Html::encode($settings->organization_name) ?></h2>
        <div style="font-size: 14px; font-weight: bold; margin-top: 10px; text-decoration: underline;"><?= Html::encode($title) ?></div>
        <div style="font-size: 10px; margin-top: 5px;">Generated: <?= date('d M Y, H:i') ?></div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => '',
        'columns' => $columns,
        'tableOptions' => $isExcel ? ['border' => '1'] : ['class' => 'report-table'],
        'emptyText' => 'No records found for this period.',
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => '<span class="text-muted">-</span>',
        ],
    ]); ?>

</body>
</html>