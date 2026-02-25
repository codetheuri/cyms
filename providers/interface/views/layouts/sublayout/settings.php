<?php
/** @var \yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;
use yii\helpers\Url;

$this->beginContent('@ui/views/layouts/dashboard.php'); // nest inside main layout
$route = Yii::$app->controller->route; // Get current route to highlight active menu
?>
<div class="content container-fluid mt-3">
    
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title fw-bold text-dark"><i class="fa fa-cogs me-2 text-muted"></i> System Settings</h3>
                <p class="text-muted fs-sm mb-0">Manage your yard configurations, billing tariffs, and email parameters.</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-3 col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush rounded">
                        <a href="<?= Url::to(['general-setting']) ?>" class="list-group-item list-group-item-action py-3 fw-medium <?= strpos($route, 'general-setting') !== false ? 'active bg-primary text-white' : 'text-dark' ?>">
                            <i class="fa fa-sliders-h fa-fw me-2 <?= strpos($route, 'general-setting') !== false ? '' : 'text-muted' ?>"></i> General Settings
                        </a>
                        <a href="<?= Url::to(['tariff-setting']) ?>" class="list-group-item list-group-item-action py-3 fw-medium <?= strpos($route, 'tariff-setting') !== false ? 'active bg-primary text-white' : 'text-dark' ?>">
                            <i class="fa fa-money-bill-wave fa-fw me-2 <?= strpos($route, 'tariff-setting') !== false ? '' : 'text-muted' ?>"></i> Tariffs & Billing
                        </a>
                        <a href="<?= Url::to(['email-setting']) ?>" class="list-group-item list-group-item-action py-3 fw-medium <?= strpos($route, 'email-setting') !== false ? 'active bg-primary text-white' : 'text-dark' ?>">
                            <i class="fa fa-envelope fa-fw me-2 <?= strpos($route, 'email-setting') !== false ? '' : 'text-muted' ?>"></i> Email Configuration
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-9 col-md-8">
            <?= $content ?>
        </div>
    </div>
</div>
<?php $this->endContent(); ?>