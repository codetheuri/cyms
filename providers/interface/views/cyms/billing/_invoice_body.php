<?php
$settingLiftOn  = (float) Yii::$app->config->get('lift_on_charges');
$settingLiftOff = (float) Yii::$app->config->get('lift_off_charges');
$currentLift    = (float) ($model->lift_charges ?? 0);

$hasLiftOff = ($currentLift >= $settingLiftOff);
$hasLiftOn  = ($currentLift >= ($settingLiftOff + $settingLiftOn)) || (abs($currentLift - $settingLiftOn) < 0.01 && !$hasLiftOff);

$storage  = (float) ($model->storage_total ?? 0);
$repair   = (float) ($model->repair_total ?? 0);
$discount = (float) ($model->discount_amount ?? 0);
$subTotal = $storage + $repair + $currentLift;
$grand    = $subTotal - $discount;
?>
<div style="height: 10px;"></div>
<div class="section-title">Invoice Details</div>
<table class="invoice-table">
    <thead>
        <tr>
            <th width="45%">Description</th>
            <th class="text-right">Details</th>
            <th class="text-right">Rate</th>
            <th class="text-right">Amount (KES)</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($storage > 0): ?>
            <tr>
                <td><strong>Storage Charges</strong></td>
                <td class="text-right"><?= $model->storage_days ?> Days</td>
                <td class="text-right"><?= number_format($model->tariff_rate, 2) ?></td>
                <td class="text-right"><?= number_format($storage, 2) ?></td>
            </tr>
        <?php endif; ?>

        <?php if ($hasLiftOff): ?>
            <tr>
                <td><strong>Lift Off (Gate In)</strong></td>
                <td class="text-right">Offloading</td>
                <td class="text-right"><?= number_format($settingLiftOff, 2) ?></td>
                <td class="text-right"><?= number_format($settingLiftOff, 2) ?></td>
            </tr>
        <?php endif; ?>

        <?php if ($hasLiftOn): ?>
            <tr>
                <td><strong>Lift On (Gate Out)</strong></td>
                <td class="text-right">Loading</td>
                <td class="text-right"><?= number_format($settingLiftOn, 2) ?></td>
                <td class="text-right"><?= number_format($settingLiftOn, 2) ?></td>
            </tr>
        <?php endif; ?>

        <?php if ($repair > 0): ?>
            <tr>
                <td><strong>Repair Charges</strong></td>
                <td class="text-right">Maintenance</td>
                <td class="text-right">—</td>
                <td class="text-right"><?= number_format($repair, 2) ?></td>
            </tr>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="text-right bold" style="padding-top: 20px;">Subtotal</td>
            <td class="text-right bold" style="padding-top: 20px;"><?= number_format($subTotal, 2) ?></td>
        </tr>
        <?php if ($discount > 0): ?>
            <tr>
                <td colspan="3" class="text-right bold danger">Discount</td>
                <td class="text-right bold danger">-<?= number_format($discount, 2) ?></td>
            </tr>
        <?php endif; ?>
        <tr class="total-row">
            <td colspan="3" class="text-right" style="font-size: 12pt;">Grand Total</td>
            <td class="text-right" style="font-size: 12pt;"><?= number_format($grand, 2) ?></td>
        </tr>
    </tfoot>
</table>

<div class="section-title">Payment History</div>
<table class="invoice-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Method / Reference</th>
            <th class="text-right">Amount (KES)</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($model->payments)): ?>
            <tr>
                <td colspan="3" align="center" style="padding: 20px;"><em>No payments received yet.</em></td>
            </tr>
        <?php else: ?>
            <?php foreach ($model->payments as $payment): ?>
                <tr>
                    <td><?= date('d M Y', strtotime($payment->transaction_date)) ?></td>
                    <td><?= htmlspecialchars($payment->method) ?> (<?= htmlspecialchars($payment->reference) ?>)</td>
                    <td class="text-right success"><?= number_format($payment->amount, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        <tr class="total-row">
            <td colspan="2" class="text-right" style="font-size: 11pt;">Balance Due</td>
            <td class="text-right <?= $model->balance > 0 ? 'danger' : 'success' ?>" style="font-size: 11pt;">
                KES <?= number_format($model->balance, 2) ?>
            </td>
        </tr>
    </tbody>
</table>