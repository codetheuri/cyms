<?php

require_once __DIR__ . '/wrapper.php';
$wrapper = new ConfigWrapper();
$config = [
    'id' => $_ENV['APP_CODE'] . '-console',
    'name' => $_ENV['APP_NAME'] . ' Terminal',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'cmd\controllers',
    'timeZone' => 'Africa/Nairobi',
    'aliases' =>  $wrapper->load('aliases'),
    'modules' => $wrapper->load('modules'),
    'runtimePath' => dirname(__DIR__) . '/providers/bin',
    'controllerMap' => [
        'voyage' => [
            'class' => 'cmd\controllers\VoyageController',
            'migrationPath' => $wrapper->load('migrationPaths'),
        ],
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'authManager' => [
            'class' => \helpers\auth\AuthManager::class,
            'cache' => 'cache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $wrapper->dbDriver('CORE_DB'),
    ],
    'params' => $wrapper->load('params'),
];

if ($_SERVER['ENVIRONMENT'] == 'dev') {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*'],
        'generators' => [
            'model' => [
                'class'     =>  \coder\api\model\Generator::class,
                'useTablePrefix' => true,
            ],
            'crud' => [
                'class'     =>  \coder\api\crud\Generator::class,
                'baseControllerClass' =>  'helpers\ApiController',
            ],
            'module' => [
                'class'     =>  \coder\api\module\Generator::class,
            ]
        ],
    ];
}
return $config;
