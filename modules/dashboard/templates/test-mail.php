// Create a file: modules/payment/templates/test-mail.php
<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $data array */

?>
<h1>Test Email</h1>
<p>This is a test email from <?= Html::encode(Yii::$app->name) ?></p>
<p>Order Number: <?= Html::encode($data['order']->order_number ?? 'TEST') ?></p>
<p>Total Amount: <?= Yii::$app->formatter->asCurrency($data['order']->total_amount ?? 0) ?></p>