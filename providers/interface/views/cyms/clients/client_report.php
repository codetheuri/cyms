<?php
use yii\helpers\Html;

/* @var $model dashboard\models\MasterContainerOwners */
/* @var $settings admin\models\static\General */
/* @var $inYard array */
/* @var $unpaid array */
/* @var $history array */
/* @var $isExcel bool */

$logoUrl = ($settings->site_logo && !$isExcel) ? Yii::getAlias('@web') . '/' . $settings->site_logo : null;

// Excel Styling Helpers
$border = $isExcel ? 'border="1"' : 'class="bordered-table"';
$bgHead = $isExcel ? 'bgcolor="#CCCCCC"' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statement - <?= Html::encode($model->owner_name) ?></title>
    <?php if (!$isExcel): ?>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        h1 { margin: 0; font-size: 22px; color: #004085; }
        h2 { font-size: 16px; margin-top: 20px; border-bottom: 1px solid #ccc; padding-bottom: 5px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        th { background: #f2f2f2; text-align: left; padding: 8px; border: 1px solid #ccc; font-size: 11px; font-weight: bold; }
        td { padding: 8px; border: 1px solid #ccc; font-size: 11px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-danger { color: #dc3545; }
        
        /* --- FIX IS HERE --- */
        /* Only hide .no-print elements when actually printing */
        @media print { 
            .no-print { display: none !important; } 
            body { padding: 0; }
        }
    </style>
    <?php endif; ?>
</head>
<body>

    <?php if (!$isExcel): ?>
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #006D44; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
            <span style="font-size: 16px;">🖨️</span> Print / Save PDF
        </button>
    </div>
    <?php endif; ?>

    <table style="border: none; width: 100%;">
        <tr>
            <?php if (!$isExcel): ?>
            <td style="border: none; width: 20%;">
                <?php if ($logoUrl): ?>
                    <img src="<?= $logoUrl ?>" style="max-height: 60px;">
                <?php endif; ?>
            </td>
            <?php endif; ?>
            <td style="border: none; text-align: center;" colspan="<?= $isExcel ? 6 : 1 ?>">
                <h1 style="font-size: 20px; font-weight: bold;"><?= Html::encode($settings->organization_name) ?></h1>
                <div><?= Html::encode($settings->physical_address) ?> | Tel: <?= Html::encode($settings->primary_mobile_number) ?></div>
                <div style="font-size: 18px; margin-top: 10px; font-weight: bold; text-decoration: underline;">CLIENT STATEMENT OF ACCOUNT</div>
            </td>
            <?php if (!$isExcel): ?><td style="border: none; width: 20%;"></td><?php endif; ?>
        </tr>
    </table>

    <div style="margin-top: 20px; margin-bottom: 20px; border: 1px solid #000; padding: 10px;">
        <strong>Client Name:</strong> <?= Html::encode($model->owner_name) ?><br>
        <strong>Contact:</strong> <?= Html::encode($model->owner_contact) ?><br>
        <strong>Email:</strong> <?= Html::encode($model->owner_email) ?><br>
        <strong>Statement Date:</strong> <?= date('d-M-Y') ?>
    </div>

    <h2 style="font-weight: bold; background-color: #eee;">1. OUTSTANDING INVOICES (UNPAID)</h2>
    <table <?= $border ?>>
        <thead>
            <tr>
                <th <?= $bgHead ?>>Invoice #</th>
                <th <?= $bgHead ?>>Container</th>
                <th <?= $bgHead ?>>Storage Days</th>
                <th <?= $bgHead ?>>Total Amount</th>
                <th <?= $bgHead ?>>Paid</th>
                <th <?= $bgHead ?>>Balance Due</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalDue = 0;
            foreach ($unpaid as $bill): 
                $totalDue += $bill->balance;
            ?>
            <tr>
                <td><?= $bill->invoice_number ?: 'DRAFT' ?></td>
                <td><?= $bill->visit->container_number ?></td>
                <td><?= $bill->storage_days ?></td>
                <td class="text-right"><?= number_format($bill->grand_total, 2) ?></td>
                <td class="text-right"><?= number_format($bill->total_paid, 2) ?></td>
                <td class="text-right" style="font-weight: bold; color: red;"><?= number_format($bill->balance, 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($unpaid)): ?>
                <tr><td colspan="6" class="text-center">No outstanding invoices.</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right"><strong>TOTAL OUTSTANDING:</strong></td>
                <td class="text-right"><strong><?= number_format($totalDue, 2) ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <h2 style="font-weight: bold; background-color: #eee;">2. CONTAINERS CURRENTLY IN YARD</h2>
    <table <?= $border ?>>
        <thead>
            <tr>
                <th <?= $bgHead ?>>Container No.</th>
                <th <?= $bgHead ?>>Ticket IN</th>
                <th <?= $bgHead ?>>Date IN</th>
                <th <?= $bgHead ?>>Days Stayed</th>
                <th <?= $bgHead ?>>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inYard as $visit): ?>
            <tr>
                <td><?= $visit->container_number ?></td>
                <td><?= $visit->ticket_no_in ?></td>
                <td><?= Yii::$app->formatter->asDate($visit->date_in) ?></td>
                <td>
                    <?php 
                        $days = (new DateTime($visit->date_in))->diff(new DateTime())->days;
                        echo $days . ' Days';
                    ?>
                </td>
                <td><?= $visit->status ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($inYard)): ?>
                <tr><td colspan="5" class="text-center">No containers currently in yard.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2 style="font-weight: bold; background-color: #eee;">3. RECENT PAYMENT HISTORY</h2>
    <table <?= $border ?>>
        <thead>
            <tr>
                <th <?= $bgHead ?>>Invoice #</th>
                <th <?= $bgHead ?>>Container</th>
                <th <?= $bgHead ?>>Last Updated</th>
                <th <?= $bgHead ?>>Amount Billed</th>
                <th <?= $bgHead ?>>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $bill): ?>
            <tr>
                <td><?= $bill->invoice_number ?></td>
                <td><?= $bill->visit->container_number ?></td>
                <td><?= Yii::$app->formatter->asDate($bill->updated_at) ?></td>
                <td class="text-right"><?= number_format($bill->grand_total, 2) ?></td>
                <td><?= $bill->status ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>