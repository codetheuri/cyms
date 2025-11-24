<?php
/* @var $visit dashboard\models\ContainerVisits */
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
    <title>Outward Interchange - <?= Html::encode($visit->container_number) ?></title>
    <style>
        /* RESET & BASICS */
        body { 
            font-family: 'Courier New', Courier, monospace; /* Monospace look like the sample */
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
        
        /* GRID LAYOUT */
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        td { padding: 4px; vertical-align: top; }
        
        /* BORDERS & BOXES */
        .box { border: 1px solid #000; padding: 5px; min-height: 35px; }
        .no-border { border: none !important; }
        
        /* TYPOGRAPHY */
        h1 { font-family: 'Arial', sans-serif; font-size: 24px; margin: 0; text-transform: uppercase; font-weight: 900; }
        .header-sub { font-family: 'Arial', sans-serif; font-size: 10px; line-height: 1.4; }
        
        .label { font-family: 'Arial', sans-serif; font-weight: bold; font-size: 9px; color: #555; text-transform: uppercase; display: block; margin-bottom: 2px; }
        .value { font-weight: bold; font-size: 12px; color: #000; }
        
        /* WATERMARK STYLE */
        .watermark {
            position: absolute;
            top: 35%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            font-size: 120px;
            font-family: 'Arial', sans-serif;
            opacity: 0.04;
            font-weight: 900;
            color: #000;
            z-index: 0;
            pointer-events: none;
        }

        /* PRINT MEDIA QUERY */
        @media print {
            body { background: #fff; padding: 0; margin: 0; }
            .page-container { width: 100%; margin: 0; padding: 10mm; box-shadow: none; }
            .no-print { display: none !important; }
            @page { margin: 0; size: A4; }
        }
    </style>
</head>
<body>

    <!-- Print Controls -->
    <div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
        <button onclick="window.print()" style="padding: 12px 24px; font-size: 16px; font-weight: bold; cursor: pointer; background: #d9534f; color: white; border: none; border-radius: 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.2);">
            🖨️ Print / Save as PDF
        </button>
    </div>

    <div class="page-container">
        
        <!-- Watermark -->
        <div class="watermark">OUTWARD</div>

        <!-- HEADER -->
        <table style="border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px;">
            <tr>
                <td width="20%">
                    <?php if ($logoData): ?>
                        <img src="<?= $logoData ?>" style="max-width: 120px; max-height: 80px;">
                    <?php else: ?>
                        <h3>LOGO</h3>
                    <?php endif; ?>
                </td>
                <td width="60%" align="center">
                    <h1><?= Html::encode($settings->organization_name) ?></h1>
                    <div class="header-sub">
                        <?= Html::encode($settings->physical_address) ?><br>
                        Tel: <?= Html::encode($settings->primary_mobile_number) ?><br>
                        Email: <?= Html::encode($settings->email_address) ?>
                    </div>
                    <div style="font-size: 14px; font-weight: bold; margin-top: 10px; text-decoration: underline;">
                        Container Interchange - Outward
                    </div>
                </td>
                <td width="20%" align="right" style="vertical-align: bottom;">
                    <h1 style="font-size: 40px; color: #d9534f;">OUT</h1>
                </td>
            </tr>
        </table>

        <!-- ROW 1 -->
        <table>
            <tr>
                <td width="60%">
                    <div class="box">
                        <div class="label">To (Destination):</div>
                        <div class="value"><?= Html::encode($visit->destination) ?></div>
                    </div>
                </td>
                <td width="40%">
                    <div class="box">
                        <div class="label">Outward EIR No:</div>
                        <div class="value"><?= Html::encode($visit->ticket_no_out) ?></div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- ROW 2 -->
        <table>
            <tr>
                <td width="33%">
                    <div class="box">
                        <div class="label">Booking / BL No:</div>
                        <div class="value"><?= Html::encode($visit->bl_number) ?></div>
                    </div>
                </td>
                <td width="33%">
                    <div class="box">
                        <div class="label">Date Out:</div>
                        <div class="value"><?= Yii::$app->formatter->asDate($visit->date_out) ?></div>
                    </div>
                </td>
                <td width="34%">
                    <div class="box">
                        <div class="label">Time Out:</div>
                        <div class="value"><?= Html::encode($visit->time_out) ?></div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- ROW 3 -->
        <table>
            <tr>
                <td width="33%">
                    <div class="box">
                        <div class="label">Container No.</div>
                        <div class="value" style="font-size: 16px;"><?= Html::encode($visit->container_number) ?></div>
                    </div>
                </td>
                <td width="33%">
                    <div class="box">
                        <div class="label">Agent / Line</div>
                        <div class="value"><?= $visit->shippingLine ? Html::encode($visit->shippingLine->line_name) : 'N/A' ?></div>
                    </div>
                </td>
                <td width="34%">
                    <div class="box">
                        <div class="label">Type / Size</div>
                        <div class="value">
                            <?= $visit->truck_type_out ? Html::encode($visit->truck_type_out) : 'HC' ?> / 40'
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- ROW 4: VESSEL INFO -->
        <table>
            <tr>
                <td width="50%">
                    <div class="box">
                        <div class="label">Vessel</div>
                        <div class="value"><?= Html::encode($visit->vessel_name) ?></div>
                    </div>
                </td>
                <td width="50%">
                    <div class="box">
                        <div class="label">Voyage</div>
                        <div class="value"><?= Html::encode($visit->voyage_number) ?></div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- ROW 5: TRUCK DETAILS -->
        <table>
            <tr>
                <td width="50%">
                    <div class="box">
                        <div class="label">Transporter / Owner</div>
                        <div class="value"><?= Html::encode($visit->truck_owner_name_out) ?></div>
                    </div>
                </td>
                <td width="25%">
                    <div class="box">
                        <div class="label">Lorry No</div>
                        <div class="value"><?= Html::encode($visit->vehicle_reg_no_out) ?></div>
                    </div>
                </td>
                <td width="25%">
                    <div class="box">
                        <div class="label">Trailer No</div>
                        <div class="value"><?= Html::encode($visit->trailer_reg_no_out) ?></div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- ROW 6: DRIVER -->
        <table>
            <tr>
                <td width="50%">
                    <div class="box">
                        <div class="label">Driver's Name</div>
                        <div class="value"><?= Html::encode($visit->driver_name_out) ?></div>
                    </div>
                </td>
                <td width="50%">
                    <div class="box">
                        <div class="label">ID / Passport No.</div>
                        <div class="value"><?= Html::encode($visit->driver_id_out) ?></div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- CONDITIONS -->
        <div style="margin-top: 15px; padding: 15px; border: 2px solid #000; background: #f9f9f9;">
            <div style="font-weight: bold; font-size: 11px; line-height: 1.6;">
                [ X ] THIS CONTAINER WAS DELIVERED IN GOOD CONDITION.<br>
                [ X ] CHECKED BY SHIPPER'S AGENT, FOUND CLEAN AND NO DAMAGE.<br>
                [ X ] SEAL NUMBER CHECKED: <u><?= Html::encode($visit->seal_number_out) ?></u>
            </div>
        </div>

        <!-- SIGNATURES -->
        <table style="margin-top: 40px;">
            <tr>
                <td width="33%">
                    <div class="label">Loading Clerk:</div>
                    <div class="value"><?= Yii::$app->user->identity->username ?></div>
                    <div style="border-bottom: 1px solid #000; margin-top: 25px;"></div>
                </td>
                <td width="33%">
                    <div class="label">Gate/Releasing Clerk:</div>
                    <div class="value">__________________</div>
                    <div style="border-bottom: 1px solid #000; margin-top: 25px;"></div>
                </td>
                <td width="33%">
                    <div class="label">Driver Sign:</div>
                    <br>
                    <div style="border-bottom: 1px solid #000; margin-top: 25px;"></div>
                </td>
            </tr>
        </table>

        <div style="margin-top: 30px; border-top: 1px dotted #999; padding-top: 5px; font-size: 9px; text-align: center; color: #555;">
            Notice: The Customer or Haulier is responsible for any loss or damage and cleanliness of the equipment after it has left our depot.
        </div>

    </div>

    <!-- Auto-Trigger Print Dialog -->
    <script>
        window.onload = function() {
            // Ensure images are loaded before printing
            setTimeout(function() {
                // Uncomment to auto-print
                // window.print();
            }, 500);
        }
    </script>
</body>
</html>