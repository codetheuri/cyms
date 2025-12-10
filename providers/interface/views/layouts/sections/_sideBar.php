<?php
use yii\helpers\Url;
?>
<nav id="sidebar" aria-label="Main Navigation">
    <!-- Side Header -->
    <div class="content-header">
        <!-- Logo -->
        <a class="fw-semibold text-dual" href="/">
            <span class="smini-visible">
                <i class="fa fa-circle-notch text-primary"></i>
            </span>
            <span class="smini-hide fs-5 tracking-wider"><?= Yii::$app->name ?></span>
        </a>
        <!-- END Logo -->

        <!-- Extra -->
        <div>
            <!-- Close Sidebar, Visible only on mobile screens -->
            <a class="d-lg-none btn btn-sm btn-alt-secondary ms-1" data-toggle="layout" data-action="sidebar_close" href="javascript:void(0)">
                <i class="fa fa-fw fa-times"></i>
            </a>
            <!-- END Close Sidebar -->
        </div>
        <!-- END Extra -->
    </div>

    <!-- Sidebar Scrolling Content -->
    <div class="js-sidebar-scroll">
        <!-- Side Navigation -->
        <div class="content-side">
            
            <!-- 1. Dynamic Menu (Loaded from menu.php) -->
            <?= \helpers\Menu::load() ?>

            <!-- 2. Manual Settings Section (At the Bottom) -->
            <ul class="nav-main">
                <li class="nav-main-heading">System Configuration</li>
                
                <li class="nav-main-item">
                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                        <i class="nav-main-link-icon fa fa-cog fa-spin"></i>
                        <span class="nav-main-link-name">Settings</span>
                    </a>
                    <ul class="nav-main-submenu">
                        <li class="nav-main-item">
                            <a class="nav-main-link" href="<?= Url::to(['/admin/settings/general-setting']) ?>">
                                <span class="nav-main-link-name">General Settings</span>
                            </a>
                        </li>
                        <!-- <li class="nav-main-item">
                            <a class="nav-main-link" href="<?= Url::to(['/admin/settings/tariff-setting']) ?>">
                                <span class="nav-main-link-name">Tariff & Billing</span>
                            </a>
                        </li> -->
                        <li class="nav-main-item">
                            <a class="nav-main-link" href="<?= Url::to(['/dashboard/shipping-line/index']) ?>">
                                <span class="nav-main-link-name">Shipping Lines</span>
                            </a>
                        </li>
                        <!-- Add Container Types if needed -->
                         <!-- <li class="nav-main-item">
                            <a class="nav-main-link" href="<?= Url::to(['/dashboard/container-type/index']) ?>">
                                <span class="nav-main-link-name">Container Types</span>
                            </a>
                        </li> -->
                    </ul>
                </li>
            </ul>
            
        </div>
    </div>
</nav>