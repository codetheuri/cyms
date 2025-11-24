<?php

use yii\helpers\Url;
use helpers\Html;

?>
<header id="page-header">
    <!-- Header Content -->
    <div class="content-header">
        <!-- Left Section -->
        <div class="d-flex align-items-center">
            <!-- Toggle Sidebar -->
            <!-- Layout API, functionality initialized in Template._uiApiLayout()-->
            <button type="button" class="btn btn-sm btn-alt-secondary me-2 d-lg-none" data-toggle="layout" data-action="sidebar_toggle">
                <i class="fa fa-fw fa-bars"></i>
            </button>
            <!-- END Toggle Sidebar -->

            <!-- Open Search Section (visible on smaller screens) -->
            <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
            <button type="button" class="btn btn-sm btn-alt-secondary d-md-none" data-toggle="layout" data-action="header_search_on">
                <i class="fa fa-fw fa-search"></i>
            </button>
            <!-- END Open Search Section -->
        </div>
        <!-- END Left Section -->

        <!-- Right Section -->
        <div class="d-flex align-items-center">
     <!-- User Dropdown -->
            <div class="dropdown d-inline-block ms-2" style="background-color: #1a1a34;">
                <button type="button" class="btn btn-sm btn-alt-secondary d-flex align-items-center"
                    id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="color: #ffffff;">
                    <i class="nav-main-link-icon si si-user" style="color: #ffffff;"></i>
                    <span class="d-none d-sm-inline-block ms-2"><?=  Yii::$app->user->identity->username; ?></span>
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
            <!-- END User Dropdown -->

        </div>
        <!-- END Right Section -->
    </div>
    <!-- END Header Content -->
</header>