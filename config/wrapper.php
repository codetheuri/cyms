<?php
class ConfigWrapper
{
    public $_aliases;
    public $_modules;
    public $_tokens;
    public $_params;
    public function __construct()
    {
        $this->_aliases = [
            '@bower' => '@vendor/bower-asset',
            '@npm'   => '@vendor/npm-asset',
            '@helpers' => '@app/providers/components',
            '@coder' => '@app/providers/code',
            '@swagger' => '@app/providers/swagger',
            '@ui' => '@app/providers/interface',
            '@cmd' => '@app/providers/console',
            '@modules' => '@app/modules',
        ];
        $this->_modules = [
            'admin' => [
                'class' => 'mdm\admin\Module',
                'controllerMap' => [
                    'assignment' => [
                        'class' => 'mdm\admin\controllers\AssignmentController',
                        /* 'userClassName' => 'app\models\User', */
                        'idField' => 'user_id',
                        'usernameField' => 'username',
                        //'searchClass' => 'app\models\UserSearch'
                    ],
                ],
            ]
        ];
        $this->_tokens = [
            '{id}' => '<id:\\d[\\d,]*>',
            '{key}' => '<key:[a-zA-Z0-9_\-\/]+>',
            '{crypt_id}' => '<crypt_id:[a-zA-Z0-9\\-]+>',
        ];
        $this->_params = [
            'pageSize' => [10 => 10, 25 => 25, 50 => 50, 100 => 100],
            'pageSizeLimit' => 100,
            'defaultPageSize' => 25,
            'adminEmail' => 'mutuavictor476@gmail.com',
            'senderEmail' => 'mutuavictor476@gmail.com',
            'senderName' => 'Victor',
            'supportEmail' => 'support@example.com',

        ];
    }
    public function load($item)
    {
        $wrapper = $routes = [];
        $wrapper['aliases'] = $this->_aliases;
        $wrapper['modules'] = $this->_modules;
        $wrapper['tokens'] = $this->_tokens;
        $wrapper['params'] = $this->_params;
        foreach (new DirectoryIterator(dirname(__DIR__) . '/modules') as $index => $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                $wrapper['aliases']['@' . $fileinfo->getFilename()] = '@app/modules/' . $fileinfo->getFilename();
                $wrapper['controllers'][] = $fileinfo->getFilename();
                $wrapper['migrationPaths'][] = '@' . $fileinfo->getFilename() . '/migrations';
                if ($fileinfo->getFilename() !== 'main') {
                    $wrapper['modules'][$fileinfo->getFilename()] = [
                        'class' => $fileinfo->getFilename() . '\\Module'
                    ];
                    if ($fileinfo->getFilename() !== 'dashboard') {
                        $dir = dirname(__DIR__) . "/modules/" . $fileinfo->getFilename() . "/routers";
                        foreach (glob("{$dir}/*.php") as $filename) {
                            $route = require($filename);
                            $routes = array_merge($routes, $route);
                        }
                        $wrapper['routes'] = $routes;
                    }
                }
            }
            $module  = '\\' . $fileinfo->getFilename() . '\\Module';
            if (property_exists($module, 'name')) {
                $wrapper['apiMenus'][] = ['title' => Yii::$app->getModule($fileinfo->getFilename())->name, 'url' => 'api/swagger', 'param' => ['mod' => $fileinfo->getFilename()]];
            }
        }
        if (!empty($wrapper['apiMenus'])) {
            $wrapper['apiMenus'] = [['title' => 'API Docs', 'icon' => 'code', 'submenus' => $wrapper['apiMenus']]];
            if ($_SERVER['ENVIRONMENT'] == 'dev') {
                $wrapper['apiMenus'][] = ['title' => 'Gii', 'icon' => 'code-fork', 'url' => '/gii'];
            }
        }
        return $wrapper[$item];
    }
    public function dbDriver($selector = null)
    {
        $connection = [
            'class' => 'yii\db\Connection',
        ];
        switch ($_SERVER[$selector . '_DRIVER']) {
            case "mssql":
                $connection = array_merge($connection, [
                    'driverName' => 'sqlsrv',
                    'dsn' => "sqlsrv:Server={$_SERVER[$selector . '_HOST']};Database={$_SERVER[$selector . '_DATABASE']}",
                ]);
                break;
            case "pgsql":
                $connection = array_merge($connection, [
                    'dsn' => "pgsql:host={$_SERVER[$selector . '_HOST']};port={$_SERVER[$selector . '_PORT']};dbname={$_SERVER[$selector . '_DATABASE']}",
                ]);
                break;
            default: // mysql
                $connection = array_merge($connection, [
                    'dsn' => "mysql:host={$_SERVER[$selector . '_HOST']};port={$_SERVER[$selector . '_PORT']};dbname={$_SERVER[$selector . '_DATABASE']}",
                ]);
        }
        $connection = array_merge($connection, [
            'username' => $_SERVER[$selector . '_USERNAME'],
            'password' => $_SERVER[$selector . '_PASSWORD'],
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 60,
            'schemaCache' => 'cache',
        ]);
        return $connection;
    }
}
