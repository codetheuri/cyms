<?php
require_once __DIR__ . '/wrapper.php';
$userMenu = [
    ['title' => 'Dashboard', 'icon' => 'home', 'url' => '/dashboard/default/index', 'permission'=>'dashboard-default-view'],
    ['title' => 'Gate IN', 'icon' => 'truck-ramp-box', 'url' => '/dashboard/visit/index', 'permission'=>'dashboard-visit-list'],
    ['title' => 'Gate Out', 'icon' => 'truck-moving', 'url' => '/dashboard/visit/out-index', 'permission'=>'dashboard-visit-list'],
    ['title' => 'Container Survey', 'icon' => 'magnifying-glass', 'url' => '/dashboard/survey/index', 'permission'=>'dashboard-survey-list'],

    ['title' => 'Yard Position', 'icon' => 'map-location-dot', 'url' => '/dashboard/yard/index','permission'=>'dashboard-yard-view'],
    ['title' => 'Billing', 'icon' => 'receipt', 'url' => '/dashboard/billing/index','permission'=>'dashboard-billing-view'],
    ['title' => 'Clients / Owners', 'icon' => 'users', 'url' => '/dashboard/container-owner/index', 'permission'=>'dashboard-container-owner-list'],
    ['title'=> 'Reports', 'icon'=>'book', 'url'=>'/dashboard/reports/index', 'permission'=>'dashboard-reports-view'],
    ['title' => 'Master Repair Codes', 'icon' => 'wrench', 'url' => '/dashboard/repair-code/index', 'permission'=>'dashboard-repair-code-list'],
    ['title' => 'Container Types', 'icon' => 'box', 'url' => '/dashboard/container-type/index', 'permission'=>'dashboard-container-type-list'],
    // ['title' => 'Shipping Lines', 'icon' => 'ship', 'url' => '/dashboard/shipping-line/index'],

    ['title' => 'IAM & Admin', 'icon' => 'shield', 'permission'=>'dashboard-profile-list', 'submenus' => [
        ['title' => 'User Management', 'url' => 'profile/index'],
        ['title' => 'Manage Roles', 'url' => 'role/index'],
        // ['title' => 'Manage Permissions', 'url' => 'permission/index'],
    ]],
    ['title' => 'Settings', 'icon' => 'cog fa-spin', 'submenus' => [
        ['title' => 'General Settings', 'url' => '/admin/settings/general-setting'],
        ['title' => 'Email Settings', 'url' => '/admin/settings/email-setting'],
        ['title' => 'Tariff & Billing', 'url' => '/admin/settings/tariff-setting'],
        ['title' => 'Shipping Lines', 'url' => '/dashboard/shipping-line/index'],
        // ['title' => 'Container Types', 'url' => '/dashboard/container-type/index'],

    ]],

];
return array_merge($userMenu);
