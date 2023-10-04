<?php

// Remove the '-template' suffix and fill in necessary fields

$params = require __DIR__ . '/params.php';
$local = require __DIR__ . '/local.php';

return [
    'components' => [
        // Database connection.
        // same as web application
        'db' => $local['components']['db'],
        // or different
        // 'db' => [
        //     'class' => 'yii\db\Connection',
        //     // set db name and host
        //     'dsn' => 'mysql:host=localhost;dbname=',
        //     'username' => '',
        //     'password' => '',
        //     'charset' => 'utf8',
        //
        //     // Schema cache options (for production environment)
        //     'enableSchemaCache' => true,
        //     'schemaCacheDuration' => 60,
        //     'schemaCache' => 'cache',
        // ],

        // SMTP credentials
        'mailer' => $local['components']['mailer'],

        'urlManager' => [
            // Setup your domain to create absolute urls
            'scriptUrl' => '',
            'baseUrl' => ''
        ]
    ],
    'params' => array_merge($params, $local['params'])
];
