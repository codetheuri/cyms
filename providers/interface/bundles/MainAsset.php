<?php

namespace ui\bundles;

use yii\web\AssetBundle;


class MainAsset extends AssetBundle
{
    public $basePath = '@ui/assets';
    public $baseUrl = '@web/providers/interface/assets';
    public $css = [
        [
            'href' => 'oneui/favicon.png',
            'rel' => 'icon',
            'sizes' => '64x64',
        ],
        'peafowl/css/theme.css',
        'peafowl/css/style.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}