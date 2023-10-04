<?php

$params = require __DIR__ . '/params.php';
$modulesConfig = require __DIR__ . '/modules.php';
$localConfig = require __DIR__ . '/local.php';

$config = [
    'id' => '',
    'basePath' => dirname(__DIR__),
    'name' => '',
    'defaultRoute' => 'main/default/index',
    'layoutPath' => '@app/modules/main/views/layouts',
    'language' => 'uk-UA',
    'sourceLanguage' => 'en-US',
    'bootstrap' => [
        'log'
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@uploadsRestricted' => '@app/uploads_restricted'
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'class' => 'app\components\User',
            'identityClass' => 'app\modules\user\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => '/auth/login'
        ],
        'errorHandler' => [
            'errorAction' => 'main/default/error',
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
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                '/' => ''
                // '<module>/<controller>/<action>' => '<module>/<controller>/<action>',
                // '<module>/<action>' => '<module>/default/<action>'
            ],
            // Multilanguage url manager
            // 'class' => 'codemix\localeurls\UrlManager',
            // 'languages' => [
            //     'ua' => 'uk-UA',
            //     'en' => 'en-EN'
            // ],
            // 'ignoreLanguageUrlPatterns' => [
            //     '#^api/#' => '#^api/#'
            // ],
            // 'enableDefaultLanguageUrlCode' => true,
        ],
        'assetManager' => [
            'bundles' => false,
        ],
        'formatter' => [
            'dateFormat' => 'dd MMMM yyyy',
            'timeFormat' => 'HH:mm',
            'datetimeFormat' => 'dd MMMM yyyy HH:mm',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' '
        ],
        'session' => [
            'class' => 'yii\web\Session',
            'timeout' => 60 * 60 * 2
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    //'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'app' => 'app.php',
                        'user' => 'user.php',
                        'project' => 'project.php'
                    ]
                ]
            ]
        ]
    ],
    'params' => $params
];

// add modules configuration
$config = yii\helpers\ArrayHelper::merge(
    $config,
    $modulesConfig
);

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

// add local configuration
$config = yii\helpers\ArrayHelper::merge(
    $config,
    $localConfig
);
return $config;