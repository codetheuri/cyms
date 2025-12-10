<?php
/* @var $visit dashboard\models\ContainerVisits */
/* @var $survey dashboard\models\ContainerSurveys */
/* @var $settings admin\models\static\General */

use yii\helpers\Html;

// --- FIX: Convert Logo to Base64 for Reliable Printing ---
$logoData = '';
if ($settings->site_logo) {
    $path = Yii::getAlias('@webroot') . '/' . $settings->site_logo;
    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $logoData = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inward Interchange - <?= Html::encode($visit->container_number) ?></title>
    <style>
        /* RESET & BASICS */
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 12px; 
            margin: 0; 
            padding: 20px; 
            background: #525659; 
        }
        .page-container { 
            background: #fff; 
            width: 210mm; 
            min-height: 297mm; 
            margin: 0 auto; 
            padding: 15mm; 
            box-shadow: 0 0 10px rgba(0,0,0,0.5); 
            position: relative; 
        }
        
        /* TABLE STYLING */
        table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        td, th { padding: 4px 6px; vertical-align: top; }
        .bordered-table td, .bordered-table th { border: 1px solid #000; }
        
        /* TYPOGRAPHY */
        h1 { font-size: 22px; margin: 0 0 5px 0; text-transform: uppercase; color: #004085; }
        h3 { font-size: 16px; margin: 0 0 5px 0; text-decoration: underline; font-weight: bold; }
        .label { font-weight: bold; font-size: 9px; color: #333; text-transform: uppercase; }
        .value { font-weight: bold; font-size: 11px; color: #000; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        /* UTILS */
        .logo { max-height: 80px; max-width: 150px; object-fit: contain; }
        .section-header { background: #eee; font-weight: bold; padding: 5px; border: 1px solid #000; text-align: center; margin-top: 10px; margin-bottom: 5px; -webkit-print-color-adjust: exact; }

        /* PRINT MEDIA QUERY */
        @media print {
            body { background: #fff; padding: 0; margin: 0; }
            .page-container { width: 100%; margin: 0; padding: 10mm; box-shadow: none; border: none; }
            .no-print { display: none !important; }
            @page { margin: 0; size: A4; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
        <button onclick="window.print()" style="padding: 12px 24px; font-size: 16px; font-weight: bold; cursor: pointer; background: #006D44; color: white; border: none; border-radius: 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.2);">
            🖨️ Print
        </button>
    </div>

    <div class="page-container">
        
        <table style="margin-bottom: 10px;">
            <tr>
                <td width="20%">
                    <?php if ($logoData): ?>
                        <img src="<?= $logoData ?>" class="logo">
                    <?php else: ?>
                        <h2 style="color: #ccc;">LOGO</h2>
                    <?php endif; ?>
                </td>
                <td width="50%" class="text-center">
                    <h1><?= Html::encode($settings->organization_name) ?></h1>
                    <div style="font-size: 10pt;">
                        <?= Html::encode($settings->physical_address) ?><br>
                        <?= Html::encode($settings->country) ?><br>
                        Tel: <?= Html::encode($settings->primary_mobile_number) ?><br>
                        Email: <?= Html::encode($settings->email_address) ?>
                    </div>
                </td>
                <td width="30%" class="text-right">
                    <h3>INWARD INTERCHANGE</h3>
                    <table style="width: 100%; font-size: 9pt; margin-top: 5px;">
                        <tr>
                            <td style="text-align: right; font-weight: bold;">TICKET NO:</td>
                            <td style="text-align: right; border-bottom: 1px dotted #000;"><?= Html::encode($visit->ticket_no_in) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: right; font-weight: bold;">DATE:</td>
                            <td style="text-align: right; border-bottom: 1px dotted #000;"><?= Yii::$app->formatter->asDate($visit->date_in) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: right; font-weight: bold;">TIME:</td>
                            <td style="text-align: right; border-bottom: 1px dotted #000;"><?= Html::encode($visit->time_in) ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div style="border-bottom: 2px solid #000; margin-bottom: 15px;"></div>

        <table class="bordered-table">
            <tr>
                <td width="50%">
                    <div class="label">Shipping Agent</div>
                    <div class="value"><?= Html::encode($visit->shipping_agent_name) ?></div>
                </td>
                <td width="50%">
                    <div class="label">Shipping Line</div>
                    <div class="value"><?= $visit->shippingLine ? Html::encode($visit->shippingLine->line_name) : 'N/A' ?></div>
                </td>
            </tr>
        </table>

        <table class="bordered-table" style="margin-top: -1px;">
            <tr>
                <td width="30%">
                    <div class="label">Container No.</div>
                    <div class="value" style="font-size: 14px;"><?= Html::encode($visit->container_number) ?></div>
                </td>
                <td width="10%">
                    <div class="label">Size</div>
                    <div class="value"><?= $visit->containerType ? Html::encode($visit->containerType->size) . "'" : 'N/A' ?></div>
                </td>
                <td width="10%">
                    <div class="label">Type</div>
                    <div class="value"><?= $visit->containerType ? Html::encode($visit->containerType->type_group) : 'N/A' ?></div>
                </td>
                <td width="25%">
                    <div class="label">Vessel</div>
                    <div class="value"><?= Html::encode($visit->vessel_name) ?></div>
                </td>
                <td width="25%">
                    <div class="label">Voyage / BL</div>
                    <div class="value"><?= Html::encode($visit->voyage_number) ?> / <?= Html::encode($visit->bl_number) ?></div>
                </td>
            </tr>
        </table>

        <table class="bordered-table" style="margin-top: 10px;">
            <tr>
                <td width="30%">
                    <div class="label">Vehicle Reg No. / Trailer</div>
                    <div class="value"><?= Html::encode($visit->vehicle_reg_no_in) ?> / <?= Html::encode($visit->trailer_reg_no_in) ?></div>
                </td>
                <td width="40%">
                    <div class="label">Driver Name / ID</div>
                    <div class="value"><?= Html::encode($visit->driver_name_in) ?> / <?= Html::encode($visit->driver_id_in) ?></div>
                </td>
                <td width="30%">
                    <div class="label">Signature</div>
                    <div style="height: 20px;"></div>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <div class="label">Transporter / Haulier</div>
                    <div class="value">
                        <?= $visit->containerOwner ? Html::encode($visit->containerOwner->owner_name) : Html::encode($visit->truck_owner_name_in) ?>
                    </div>
                </td>
            </tr>
        </table>

        <table class="bordered-table" style="margin-top: -1px;">
            <tr>
                <td width="25%">
                    <div class="label">Max Gross Weight</div>
                    <div class="value"><?= $visit->gross_weight ? number_format($visit->gross_weight) . ' KG' : '' ?></div>
                </td>
                <td width="25%">
                    <div class="label">Tare Weight</div>
                    <div class="value"><?= $visit->tare_weight ? number_format($visit->tare_weight) . ' KG' : '' ?></div>
                </td>
                <td width="25%">
                    <div class="label">Payload</div>
                    <div class="value"><?= $visit->payload ? number_format($visit->payload) . ' KG' : '' ?></div>
                </td>
               
            </tr>
        </table>

        <div class="section-header">
            CONDITION REPORT / DAMAGES
        </div>
        
        <table class="bordered-table" style="margin-top: -1px;">
            <thead>
                <tr style="background: #f0f0f0;">
                    <th width="15%" class="text-left">Code</th>
                    <th width="45%" class="text-left">Description</th>
                    <th width="10%" class="text-center">Qty</th>
                    <th width="15%" class="text-right">Labour</th>
                    <th width="15%" class="text-right">Material</th>
                    <th width="15%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grandTotal = 0;
                if ($survey && !empty($survey->surveyDamages)): 
                    foreach ($survey->surveyDamages as $damage): 
                        $grandTotal += $damage->total_cost;
                ?>
                    <tr>
                        <td><?= Html::encode($damage->repair_code) ?></td>
                        <td><?= Html::encode($damage->description) ?></td>
                        <td class="text-center"><?= $damage->quantity ?></td>
                        <td class="text-right"><?= number_format($damage->labor_cost, 2) ?></td>
                        <td class="text-right"><?= number_format($damage->material_cost, 2) ?></td>
                        <td class="text-right"><?= number_format($damage->total_cost, 2) ?></td>
                    </tr>
                <?php 
                    endforeach; 
                else: 
                ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 15px; color: #666;">
                            <i>CONTAINER RECEIVED IN SOUND CONDITION</i>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if ($grandTotal > 0): ?>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right bold">TOTAL REPAIR COST:</td>
                    <td class="text-right bold"><?= number_format($grandTotal, 2) ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>

        <div style="text-align: center; font-weight: bold; margin: 5px 0; font-size: 11px;">
            THIS CONTAINER WAS RECEIVED IN GOOD CONDITION EXCEPT AS NOTED ABOVE.
        </div>

        <table class="bordered-table" style="margin-top: 10px; width: 100%;">
            <tr>
                <td style="width: 35%; height: 60px; vertical-align: top;">
                    <div class="label">CONTAINER SURVEYED BY:</div>
                    <div class="value" style="margin-top: 5px;">
                        <?= $survey ? Html::encode($survey->surveyor_name) : '' ?>
                    </div>
                </td>
                <td style="width: 35%; vertical-align: top;">
                    <div class="label">EIR PREPARED BY:</div>
                    <div class="value" style="margin-top: 5px;">
                        <?= Yii::$app->user->identity->username ?>
                    </div>
                </td>
                <td style="width: 30%; vertical-align: top;">
                    <div class="label">SIGNATURE:</div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="vertical-align: top; height: 70px;">
                    <div class="label">REMARKS:</div>
                    <div class="value" style="margin-top: 5px; font-family: monospace; white-space: pre-wrap;">
                        <?= $visit->comments_in ? Html::encode($visit->comments_in) : 'N/A' ?>
                    </div>
                </td>
                <td style="vertical-align: top;">
                    <div class="label">MCD SIGN / STAMP:</div>
                </td>
            </tr>
        </table>
        
        <div style="margin-top: 20px; font-size: 10px; text-align: center; color: #777;">
            Container surveyed and repaired as per IICL Standards.
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                // window.print();
            }, 500);
        }
    </script>
</body>
</html>