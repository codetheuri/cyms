<?php
/** @var $model dashboard\models\BillingRecords */
?>
<div style="font-family: Arial, sans-serif;">
    <table width="100%" style="border-bottom: 3px solid #2c3e50; padding-bottom: 10px;">
        <tr>
            <td width="50%" style="vertical-align: top;">
                <div style="font-size: 20pt; font-weight: bold; color: #2c3e50; text-transform: uppercase;">
                    <?= htmlspecialchars(\Yii::$app->config->get('organization_name')) ?>
                </div>
                <div style="font-size: 10pt; color: #7f8c8d; margin-top: 5px;">
                   <?= Yii::$app->config->get('physical_address') ?>
                </div>
            </td>
            <td width="50%" align="right" style="vertical-align: top; line-height: 1.6;">
                <div style="font-size: 16pt; font-weight: bold; color: #2c3e50;">INVOICE</div>
                <div style="font-size: 10pt;">
                    <strong>No:</strong> <?= $model->invoice_number ?><br>
                    <strong>Date:</strong> <?= date('d M Y') ?><br>
                    <strong>Status:</strong> 
                    <span style="font-weight: bold; color: <?= ($model->status === 'PAID') ? '#27ae60' : '#e74c3c' ?>;">
                        <?= strtoupper($model->status) ?>
                    </span>
                </div>
            </td>
        </tr>
    </table>
</div>