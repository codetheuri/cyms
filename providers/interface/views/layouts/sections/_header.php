<?php

use yii\helpers\Url;
use helpers\Html;

// 1. Get Notification Count
// (Checks for any billing records waiting for credit approval)
$pendingCount = \dashboard\models\BillingRecords::find()
    ->where(['approval_status' => 'PENDING'])
    ->count();
?>
<header id="page-header">
    <div class="content-header">
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-sm btn-alt-secondary me-2 d-lg-none" data-toggle="layout" data-action="sidebar_toggle">
                <i class="fa fa-fw fa-bars"></i>
            </button>
            <button type="button" class="btn btn-sm btn-alt-secondary d-md-none" data-toggle="layout" data-action="header_search_on">
                <i class="fa fa-fw fa-search"></i>
            </button>
            </div>
        <div class="d-flex align-items-center">
            
            <div class="dropdown d-inline-block me-2">
                <button type="button" class="btn btn-sm btn-alt-secondary" id="page-header-notifications-dropdown"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-fw fa-bell"></i>
                    
                    <?php if ($pendingCount > 0): ?>
                        <span class="badge bg-danger rounded-pill badge-notification animate-pulse">
                            <?= $pendingCount ?>
                        </span>
                    <?php endif; ?>
                </button>
                
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0 border-0"
                    aria-labelledby="page-header-notifications-dropdown">
                    <div class="p-2 bg-primary-dark text-center rounded-top">
                        <h5 class="dropdown-header text-uppercase text-white">Notifications</h5>
                    </div>
                    <ul class="nav-items mb-0">
                        <?php if ($pendingCount > 0): ?>
                            <li>
                                <a class="text-dark d-flex py-2" href="<?= Url::to(['/dashboard/billing/credit-requests']) ?>">
                                    <div class="flex-shrink-0 me-2 ms-3">
                                        <i class="fa fa-hand-holding-usd text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1 pe-2">
                                        <div class="fw-semibold">Credit Requests</div>
                                        <span class="fw-medium text-muted">
                                            <?= $pendingCount ?> container(s) waiting approval
                                        </span>
                                    </div>
                                </a>
                            </li>
                        <?php else: ?>
                            <li>
                                <div class="text-center py-4 text-muted fs-sm">
                                    <i class="fa fa-check-circle mb-1"></i><br>
                                    No new notifications
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <?php if ($pendingCount > 0): ?>
                        <div class="p-2 border-top">
                            <a class="btn btn-sm btn-light w-100 text-center" href="<?= Url::to(['/dashboard/billing/credit-requests']) ?>">
                                <i class="fa fa-eye opacity-50 me-1"></i> View All
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="dropdown d-inline-block ms-2" style="background-color: #1a1a34;">
                <button type="button" class="btn btn-sm btn-alt-secondary d-flex align-items-center"
                    id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="color: #ffffff;">
                    <i class="nav-main-link-icon si si-user" style="color: #ffffff;"></i>
                    <span class="d-none d-sm-inline-block ms-2"><?= Yii::$app->user->identity->username; ?></span>
                    <i class="fa fa-fw fa-angle-down d-none d-sm-inline-block opacity-50 ms-1 mt-1" style="color: #ffffff;"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-md dropdown-menu-end p-0 border-0"
                    aria-labelledby="page-header-user-dropdown">
                    <div class="p-2">
                        <a class="dropdown-item d-flex align-items-center justify-content-between"
                            href="<?= Url::to(['/dashboard/iam/logout']) ?>">
                            <span class="fs-sm fw-medium" style="color: #1a1a34;">Log Out</span>
                        </a>
                    </div>
                    <div class="p-2">
                        <?= Html::customButton([
                            'type' => 'modal',
                            'url' => Url::to(['/dashboard/iam/change-password']),
                            'appearence' => [
                                'type' => 'text',
                                'text' => 'change password',
                                'theme' => 'none',
                            ],
                            'modal' => ['title' => 'change password']
                        ]) ?>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </header>