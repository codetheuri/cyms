<?php
use yii\helpers\Html;
use yii\helpers\Url;
use crm\models\DeliveryAddress;

/* @var $this \yii\web\View */
/* @var $data array */
/* @var $order \crm\models\Orders */

$order = $data['order'];
$customerName = $data['customerName'];
$orderItems = $data['orderItems'] ?? [];
$deliveryAddress = $data['deliveryAddress'] ?? null;
// Safely access properties with null coalescing
$orderNumber = $order->order_number ?? 'N/A';
$orderDate = $order->created_at ?? time();
$totalAmount = $order->total_amount ?? 0;

// Fetch delivery address safely



// Safely access customer properties
$customerFullName = $customerName;
$mobileNumber = 'Not provided';

// This is the problematic code block, which we are now fixing.
if (isset($order->customer)) {
    // We are no longer calling getFullName() on the customer object.
    $customerFullName = $customerName; 
    if (isset($order->customer->profile)) {
        $mobileNumber = $order->customer->profile->mobile_number ?? 'Not provided';
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Order Confirmation: #<?= Html::encode($orderNumber) ?></title>
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background-color: #f6f9fc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 16px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #6a1f02ff 0%, #b65a35ff 100%);
            color: white;
            padding: 40px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: white;
            padding: 40px;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #2a9d8f;
        }
        .summary-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .address-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #842f1aff 0%, #653b29ff 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 14px;
        }
        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }
            .header,
            .content {
                padding: 20px;
            }
            .summary-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0; font-size: 28px;">Thank You For Your Order!</h1>
            <p style="margin: 10px 0 0; font-size: 16px; opacity: 0.9;">Your order has been placed successfully</p>
        </div>
        
        <div class="content">
            <div class="section">
                <p>Hello <strong><?= Html::encode($customerName) ?></strong>,</p>
                <p>We're happy to let you know that we've received your order and are preparing it for shipment. You can find your order details below.</p>
            </div>

            <!-- Order Summary -->
            <div class="section">
                <div class="section-title">Order Summary</div>
                <div class="summary-box">
                    <div class="summary-row">
                        <span>Order Number:</span>
                        <span><strong><?= Html::encode($orderNumber) ?></strong></span>
                    </div>
                    <div class="summary-row">
                        <span>Order Date:</span>
                        <span><?= Yii::$app->formatter->asDate($orderDate) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Total Amount:</span>
                        <span><strong><?= Yii::$app->formatter->asCurrency($totalAmount) ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <?php if (!empty($orderItems)): ?>
            <div class="section">
                <div class="section-title">Order Items</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Price</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td>
                                <?= Html::encode($item->product->name ?? 'Product') ?>
                                <?php if (isset($item->product_variant_option_id) && $item->product_variant_option_id): ?>
                                <br><small style="color: #6c757d;">Variant: <?= Html::encode($item->variantOption->variantOptions->value ?? 'N/A') ?></small>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;"><?= Html::encode($item->quantity ?? 1) ?></td>
                            <td style="text-align: right;"><?= Yii::$app->formatter->asCurrency($item->unit_price ?? 0) ?></td>
                            <td style="text-align: right;"><?= Yii::$app->formatter->asCurrency($item->total_price ?? 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right; font-weight: bold; padding-top: 15px;">Total:</td>
                            <td style="text-align: right; font-weight: bold; padding-top: 15px;"><?= Yii::$app->formatter->asCurrency($totalAmount) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>

            <!-- Shipping Address -->
            <div class="section">
                <div class="section-title">Shipping Address</div>
                <div class="address-box">
                    <!-- Corrected line here -->
                    <p style="margin: 0 0 10px 0;"><strong><?= Html::encode($customerFullName) ?></strong></p>
                    <?php if ($deliveryAddress): ?>
                    <p style="margin: 0;">
                        <?= Html::encode($deliveryAddress->address ?? '') ?><br>
                        <?= Html::encode($deliveryAddress->city ?? '') ?>, <?= Html::encode($deliveryAddress->postal_code ?? '') ?><br>
                        Phone: <?= Html::encode($mobileNumber) ?>
                    </p>
                    <?php else: ?>
                    <p style="margin: 0; color: #6c757d;">No delivery address provided.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Call to Action -->
            <?php if (isset($order->id)): ?>
            <div class="section" style="text-align: center;">
                <a href="<?= Url::to(['/crm/customers/profile#w0-tab1', 'id' => $order->id], true) ?>" class="btn">View Your Order</a>
            </div>
            <?php endif; ?>

            <!-- Support Info -->
            <div class="section" style="text-align: center; padding-top: 20px; border-top: 1px solid #e9ecef;">
                <p style="color: #6c757d; margin: 0;">
                    Need help? Contact our support team at <a href="mailto:support@<?= Yii::$app->name ?>.com" style="color: #2a9d8f;">support@<?= Yii::$app->name ?>.com</a>
                </p>
            </div>
        </div>

        <div class="footer">
            &copy; <?= date('Y') ?> <?= Html::encode(Yii::$app->name) ?>. All rights reserved.
        </div>
    </div>
</body>
</html>