<?php

$menus = [
    [
        'title' => 'Account Settings',
        'url' => '/dashboard/profile/settings',
        'icon' => 'fa-solid fa-user-gear',
        'activeOn' => 'settings',
        'roles' => ['su', 'user'], // both su & user can see
    ],
    [
        'title' => 'Change Password',
        'url' => '/dashboard/iam/change-password',
        'icon' => 'fa-solid fa-key',
        'activeOn' => 'template-invoice',
        'roles' => ['su', 'user'],
    ],
    // [
    //     'title' => 'Company Settings',
    //     'url' => '/admin/settings/company-setting',
    //     'icon' => 'fa-solid fa-gear',
    //     'activeOn' => 'company-settings',
    //     'roles' => ['su'], // only su
    // ],

    // [
    //     'title' => 'Payment Methods',
    //     'url' => '/admin/settings/payment-setting',
    //     'icon' => 'fa-solid fa-comment-dollar',
    //     'activeOn' => 'template-invoice',
    //     'roles' => ['su'],
    // ],
    // [
    //     'title' => 'Email Settings',
    //     'url' => '/admin/settings/mail-setting',
    //     'icon' => 'fa-solid fa-envelope',
    //     'activeOn' => 'template-invoice',
    //     'roles' => ['su'],
    // ],

];

return $menus;
