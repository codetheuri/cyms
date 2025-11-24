<?php

namespace helpers;


class DashboardController extends  \yii\web\Controller
{
    public $layout = 'dashboard';
    public function behaviors()
    {
        $access = [
            'as access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['register', 'login', 'error', 'request-password-reset', 'reset-password'],
                        'allow' => true,
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
        return array_merge(parent::behaviors(), $access);
    }
}
