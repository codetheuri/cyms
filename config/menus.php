<?php
require_once __DIR__ . '/wrapper.php';
$userMenu = [
    ['title' => 'Dashboard', 'icon' => 'home', 'url' => '/dashboard/default/index'],
    ['title' => 'Gate IN', 'icon' => 'truck-ramp-box', 'url' => '/dashboard/visit/index'],
    ['title' => 'Gate Out', 'icon' => 'truck-moving', 'url' => '/dashboard/visit/out-index'],
    ['title' => 'Container Survey', 'icon' => 'magnifying-glass', 'url' => '/dashboard/survey/index'],

    ['title' => 'Yard Position', 'icon' => 'map-location-dot', 'url' => '/dashboard/yard/index'],
    ['title' => 'Billing', 'icon' => 'receipt', 'url' => '/dashboard/billing/index'],
    ['title' => 'Clients / Owners', 'icon' => 'users', 'url' => '/dashboard/container-owner/index'],
    ['title'=> 'Reports', 'icon'=>'book', 'url'=>'/dashboard/reports/index'],
    ['title' => 'Master Repair Codes', 'icon' => 'wrench', 'url' => '/dashboard/repair-code/index'],
    ['title' => 'Container Types', 'icon' => 'box', 'url' => '/dashboard/container-type/index'],
    // ['title' => 'Shipping Lines', 'icon' => 'ship', 'url' => '/dashboard/shipping-line/index'],

    ['title' => 'IAM & Admin', 'icon' => 'shield', 'submenus' => [
        ['title' => 'User Management', 'url' => 'profile/index'],
        ['title' => 'Manage Roles', 'url' => 'role/index'],
        // ['title' => 'Manage Permissions', 'url' => 'permission/index'],
    ]],
    // ['title' => 'Settings', 'icon' => 'cog fa-spin', 'submenus' => [
    //     ['title' => 'General Settings', 'url' => '/admin/settings/general-setting'],
    //     // ['title' => 'Email Settings', 'url' => '/admin/settings/email-setting'],
    //     ['title' => 'Tariff & Billing', 'url' => '/admin/settings/tariff-setting'],
    //     ['title' => 'Shipping Lines', 'url' => '/dashboard/shipping-line/index'],
    //     // ['title' => 'Container Types', 'url' => '/dashboard/container-type/index'],

    // ]],

];
return array_merge($userMenu);
