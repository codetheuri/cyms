<?php

namespace ui\bundles;

use yii\web\AssetBundle;

class DashboardAsset extends AssetBundle
{
    public $basePath = '@ui/assets';
    public $baseUrl = '@web/providers/interface/assets';
    public $css = [
        [
            'href' => 'oneui/favicon.png',
            'rel' => 'icon',
            'sizes' => '64x64',
        ],
        'oneui/css/dashboard.css',
        'others/style.css',
    ];
    public $js = [
        'oneui/js/dashboard.js',
    ];
    public $depends = [
        'helpers\widgets\swal\AlertAsset',
        'yii\web\YiiAsset',
    ];
}