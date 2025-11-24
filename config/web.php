<?php

require_once __DIR__ . '/wrapper.php';
$wrapper = new ConfigWrapper();
$config = [
    'id' => $_ENV['APP_CODE'],
    'name' => $_ENV['APP_NAME'],
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'main\controllers',
    'timeZone' => 'Africa/Nairobi',
    'aliases' =>  $wrapper->load('aliases'),
    'modules' => $wrapper->load('modules'),
    'runtimePath' => dirname(__DIR__) . '/providers/bin',
    'components' => [
          'config' => [
            'class' => 'admin\hooks\Settings'
        ],
        'assetManager' => [
            'basePath' => '@ui/assets/core',
            'baseUrl' => '@web/providers/interface/assets/core',
            'bundles' => [
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'js' => []
                ],
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => [],
                ],

            ],
        ],
        'view' => [
            'theme' => [
                'basePath' => '@ui/views',
                'pathMap' => [
                    '@app/views' => '@ui/views',
                    '@dashboard/views' => '@ui/views',
                   
                ],
            ],
        ],
        'request' => [
            'cookieValidationKey' => hash_hmac('sha256', md5(date('DYM')), sha1(date('myd'))),
            // 'parsers' => [
            //     'application/json' => 'yii\web\JsonParser',
            // ],
        ],
        // 'response' => [
        //     /* Enable JSON Output: */
        //     'class' => 'yii\web\Response',
        //     'format' => \yii\web\Response::FORMAT_JSON,
        //     'charset' => 'UTF-8',
        //     'on beforeSend' => function ($event) {
        //         $response = $event->sender;
        //         if ($response->data !== null && is_array($response->data)) {
        //             /* delete code param */
        //             if (array_key_exists('code', $response->data)) {
        //                 unset($response->data['code']);
        //             }
        //             /* change status to statusCode */
        //             if (array_key_exists('status', $response->data)) {
        //                 $response->data['statusCode'] = $response->data['status'];
        //                 unset($response->data['status']);
        //             }
        //         }
        //     },
        // ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'class' => \helpers\auth\AuthUser::class,
            'identityClass' => 'auth\models\User',
            'enableAutoLogin' => false,
            'loginUrl' => ['dashboard/iam/login'],
            'identityCookie' => ['name' => '_identity-' . $_ENV['APP_CODE'], 'httpOnly' => true]
        ],
        'authManager' => [
            'class' => \helpers\auth\AuthManager::class,
            'cache' => 'cache',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        // 'mailer' => [
        //     'class' => \yii\symfonymailer\Mailer::class,
        //     'viewPath' => '@app/mail',
        //     'useFileTransport' => true,
        // ],
        // 'mailer' => [
        //     'class' => 'yii\swiftmailer\Mailer',
        //     'viewPath' => '@app/mail',
        //     'useFileTransport' => false,
        //     'transport' => [
        //               'class' => 'Swift_SmtpTransport',
        //               'host' => 'smtp.gmail.com',
        //               'username' => 'mutuavictor476@gmail.com',
        //               'password' => "bdgv mitc grnc kgbm",
        //               'port' => '587',
        //               'encryption' => 'tls',
        //           ],
        // ],
        // 
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'transport' => [
                'class' => \Swift_SmtpTransport::class,
                'host' => 'smtp.gmail.com',
                'username' => 'mutuavictor476@gmail.com',
                'password' => 'bdgv mitc grnc kgbm',
                'port' => 465,
                'scheme' => 'smtps',
                'encryption' => 'tls',
            ],
            'viewPath' => '@app/mail',
            'useFileTransport' => false,
        ],
        
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $wrapper->dbDriver('CORE_DB'),
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
                $_ENV['APP_VERSION'] . '/about' => 'site/about',
                'admin' => '/dashboard/iam/login',
                [
                    'pattern' => $_ENV['APP_VERSION'] . '/docs/openapi-json-resource',
                    'route' => 'site/json-docs',
                    'suffix' => '.json'
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'prefix' =>  $_ENV['APP_VERSION'],
                    'controller' => $wrapper->load('controllers'),
                    'extraPatterns' => $wrapper->load('routes'),
                    'tokens' =>  $wrapper->load('tokens'),
                ],
            ],
        ],
    ],
    'params' => $wrapper->load('params'),
    'defaultRoute'=>'/dashboard/default/index',  
];
if ($_SERVER['ENVIRONMENT'] == 'dev') {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*'],
        'generators' => [
            'crud' => [
                'class'     =>  \coder\mono\crud\Generator::class,
                'baseControllerClass' =>  'helpers\DashboardController',
            ],
            /* 'module' => [
                'class'     =>  \coder\api\module\Generator::class,
            ] */
        ],
    ];
}
array_push($config,);
return $config;
