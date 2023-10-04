<?php

// Remove the '-template' suffix and fill in necessary fields

return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            // set db name and host
            'dsn' => 'mysql:host=localhost;dbname=',
            'username' => '',
            'password' => '',
            'charset' => 'utf8',

            // Schema cache options (for production environment)
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 60,
            'schemaCache' => 'cache',
        ],
        // SMTP service
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => '',
                'username' => '',
                'password' => '',
                'port' => '587',
                'encryption' => 'tls',
            ],
        ],
        // or use Mailgun mailer
        // 'mailer' => [
        //     'class' => \YarCode\Yii2\Mailgun\Mailer::class,
        //     'domain' => '',
        //     'apiKey' => ''
        // ],
        // Google reCaptcha config
        'reCaptcha' => [
            'name' => 'reCaptcha',
            'class' => 'himiklab\yii2\recaptcha\ReCaptcha',
            'siteKey' => '',
            'secret' => '',
        ]
    ],
    'params' => [
        // Email address for all the automatic notifications
        'supportEmail' => 'support@localhost',
        // Google maps api key
        'google_api_key' => '',
        'gearProducers' => [
            // 'Sony',
            // 'Samsung',
            // 'etc'
        ]
    ]
];
