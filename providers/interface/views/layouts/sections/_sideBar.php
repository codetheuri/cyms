<?php
use yii\helpers\Url;
use helpers\Html;
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
       

           
       
            
            
            <!-- <?= \helpers\Menu::load() ?> -->
              <?= \helpers\PermissionMenu::load() ?>

        </div>
    </div>
</nav>