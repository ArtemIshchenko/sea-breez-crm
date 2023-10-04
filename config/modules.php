<?php
// Modules configuration
$config = [
    // Modules ist
    'modules' => [
        'externalApi' => [
            'class' => 'app\modules\externalApi\Module',
            'controllerNamespace' => 'app\modules\user\controllers\api',
            'modules' => [
                'auth' => [
                    'class' => 'app\modules\externalApi\auth\Module',
                    'controllerNamespace' => 'app\modules\user\controllers\api'
                ],
            ]
        ],
        'api' => [
            'class' => 'app\modules\api\Module',
            'modules' => [
                // 'main' => [
                //     'class' => 'app\modules\main\Module',
                //     'controllerNamespace' => 'app\modules\main\controllers\api'
                // ],
                'user' => [
                    'class' => 'app\modules\user\Module',
                    'controllerNamespace' => 'app\modules\user\controllers\api'
                ],
                'customer' => [
                    'class' => 'app\modules\customer\Module',
                    'modules' => [
                        'project' => [
                            'class' => 'app\modules\project\Module',
                            'controllerNamespace' => 'app\modules\project\controllers\api\customer'
                        ]
                    ]
                ],
                'admin' => [
                    'class' => 'app\modules\admin\Module',
                    'modules' => [
                        'user' => [
                            'class' => 'app\modules\user\Module',
                            'controllerNamespace' => 'app\modules\user\controllers\api\admin'
                        ],
                        'project' => [
                            'class' => 'app\modules\project\Module',
                            'controllerNamespace' => 'app\modules\project\controllers\api\admin'
                        ],
                        'gear' => [
                            'class' => 'app\modules\gear\Module',
                            'controllerNamespace' => 'app\modules\gear\controllers\api\admin'
                        ]
                    ]
                ],
                'manager' => [
                    'class' => 'app\modules\manager\Module',
                    'modules' => [
                        'user' => [
                            'class' => 'app\modules\user\Module',
                            'controllerNamespace' => 'app\modules\user\controllers\api\manager'
                        ],
                        'project' => [
                            'class' => 'app\modules\project\Module',
                            'controllerNamespace' => 'app\modules\project\controllers\api\manager'
                        ],
                        'gear' => [
                            'class' => 'app\modules\gear\Module',
                            'controllerNamespace' => 'app\modules\gear\controllers\api\manager'
                        ]
                    ]
                ],
                'designer' => [
                    'class' => 'app\modules\designer\Module',
                    'modules' => [
                        'user' => [
                            'class' => 'app\modules\user\Module',
                            'controllerNamespace' => 'app\modules\user\controllers\api\designer'
                        ],
                        'project' => [
                            'class' => 'app\modules\project\Module',
                            'controllerNamespace' => 'app\modules\project\controllers\api\designer'
                        ],
                        'gear' => [
                            'class' => 'app\modules\gear\Module',
                            'controllerNamespace' => 'app\modules\gear\controllers\api\designer'
                        ]
                    ]
                ]
            ]
        ],
        'main' => [
            'class' => 'app\modules\main\Module',
            'controllerNamespace' => 'app\modules\main\controllers',
            'viewPath' => '@app/modules/main/views'
        ],
        'user' => [
            'class' => 'app\modules\user\Module',
            'controllerNamespace' => 'app\modules\user\controllers',
            'viewPath' => '@app/modules/user/views'
        ]
    ],
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' =>  [
                // User module
                [
                    'class' => 'yii\web\UrlRule',
                    'pattern' => 'auth/<action>',
                    'route' => 'user/auth/<action>'
                ],
                // 1C module
                'GET api/external/get-users' => 'externalApi/auth/external/get-users',
                'POST api/external/add-specification' => 'externalApi/auth/external/add-specification',
                'POST api/external/assign-manager' => 'externalApi/auth/external/assign-manager',
                'POST api/external/assign_engineer' => 'externalApi/auth/external/assign-designer',
                'POST api/external/decline_project' => 'externalApi/auth/external/decline-project',
                'POST api/external/finish_project' => 'externalApi/auth/external/finish-project',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/external/projects' => 'externalApi'],
                    'extraPatterns' => [
                        'GET <projectId:\d+>/files'=> 'auth/external/get-files',
                        'GET <projectId:\d+>/files/<id:\d+>'=> 'external/download-file',
                        'GET <projectId:\d+>/history'=> 'auth/external/get-history',
                    ]
                ],
                'api/auth/<action>' => 'api/user/auth/<action>',
                'PATCH api/profile' => 'api/user/profile/update',
                'PATCH api/profile/verify-phone' => 'api/user/profile/verify-phone',
                'PATCH api/profile/send-code' => 'api/user/profile/send-code',
                'GET api/profile/reset-code' => 'api/user/profile/reset-code',
                'PATCH api/profile/<action:password|email|lang>' => 'api/user/profile/update-<action>',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/admin/users' => 'api/admin/user/default'],
                    'except' => ['create', 'delete'],
                    'extraPatterns' => [
                        'PATCH <id:\d+>/status' => 'update-status',
                        'PATCH check-guid' => 'check-guid',
                        'PATCH <id:\d+>/store-1c' => 'store-1c',
                        'POST <id:\d+>/comment' => 'add-comment',
                        'POST <id:\d+>/assign-new-password' => 'assign-new-password',
                        'POST <id:\d+>/send-new-password' => 'send-new-password',
                        'PATCH <id:\d+>/manager' => 'set-manager',
                        'GET list' => 'get-list',
                        'GET <id:\d+>/history' => 'get-history'
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/manager/customers' => 'api/manager/user/default'],
                    'except' => ['create', 'delete'],
                    'extraPatterns' => [
                        'POST <id:\d+>/comment' => 'add-comment',
                        'PATCH check-guid' => 'check-guid',
                        'PATCH <id:\d+>/store-1c' => 'store-1c',
                    ]
                ],
                'GET api/manager/users/list' => 'api/manager/user/default/get-list',
                'GET api/designer/users/<id:\d+>' => 'api/designer/user/default/view',

                // Project module
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/customer/projects' => 'api/customer/project/default'],
                    'extraPatterns' => [
                        'PATCH <id:\d+>/status' => 'update-status',
                        'PATCH <id:\d+>/update-onec' => 'update-onec',
                        'GET <id:\d+>/history' => 'get-history'
                    ]
                ],
                'POST api/customer/projects/<projectId:\d+>/files' => 'api/customer/project/file/create',
                'DELETE api/customer/projects/<projectId:\d+>/files/<id>' => 'api/customer/project/file/delete',
                'GET api/customer/projects/<projectId:\d+>/files/<id>' => 'api/customer/project/file/download',
                'GET api/customer/projects/<projectId:\d+>/file-tune' => 'api/customer/project/file/tune',
                'GET api/customer/projects/faq-info/<type:\d+>' => 'api/customer/project/default/faq-info',
                'GET api/customer/projects/<projectId:\d+>/imported-files' => 'api/customer/project/file/imported',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/admin/projects' => 'api/admin/project/default'],
                    'except' => ['create', 'delete'],
                    'extraPatterns' => [
                        'POST,PATCH <id:\d+>/status' => 'update-status',
                        'POST <id:\d+>/comment' => 'add-comment',
                        'PATCH <id:\d+>/designer' => 'set-designer',
                        'PATCH <id:\d+>/update-onec' => 'update-onec',
                        'GET <id:\d+>/history' => 'get-history'
                    ]
                ],
                'POST api/admin/projects/<projectId:\d+>/files' => 'api/admin/project/file/create',
                'DELETE api/admin/projects/<projectId:\d+>/files/<id>' => 'api/admin/project/file/delete',
                'GET api/admin/projects/<projectId:\d+>/files/<id:\d+>' => 'api/admin/project/file/download',
                'GET api/admin/projects/<projectId:\d+>/file-tune' => 'api/admin/project/file/tune',
                'POST api/admin/projects/<projectId:\d+>/gears' => 'api/admin/project/gear/add',
                'DELETE api/admin/projects/<projectId:\d+>/gears/<id:\d+>' => 'api/admin/project/gear/remove',
                'GET api/admin/projects/<projectId:\d+>/gears' => 'api/admin/project/gear/index',
                'GET api/admin/projects/faq-info/<type:\d+>' => 'api/admin/project/default/faq-info',
                'GET api/admin/projects/<projectId:\d+>/imported-files' => 'api/admin/project/file/imported',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/manager/projects' => 'api/manager/project/default'],
                    'except' => ['create', 'delete'],
                    'extraPatterns' => [
                        'POST,PATCH <id:\d+>/status' => 'update-status',
                        'POST <id:\d+>/comment' => 'add-comment',
                        'PATCH <id:\d+>/designer' => 'set-designer',
                        'GET <id:\d+>/history' => 'get-history'
                    ]
                ],
                'POST api/manager/projects/<projectId:\d+>/files' => 'api/manager/project/file/create',
                'DELETE api/manager/projects/<projectId:\d+>/files/<id>' => 'api/manager/project/file/delete',
                'GET api/manager/projects/<projectId:\d+>/files/<id>' => 'api/manager/project/file/download',
                'POST api/manager/projects/<projectId:\d+>/gears' => 'api/manager/project/gear/add',
                'DELETE api/manager/projects/<projectId:\d+>/gears/<id:\d+>' => 'api/manager/project/gear/remove',
                'GET api/manager/projects/<projectId:\d+>/gears' => 'api/manager/project/gear/index',
                'GET api/manager/projects/faq-info/<type:\d+>' => 'api/manager/project/default/faq-info',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/designer/projects' => 'api/designer/project/default'],
                    'except' => ['create', 'delete', 'update'],
                    'extraPatterns' => [
                        'POST <id:\d+>/status' => 'update-status',
                        'POST <id:\d+>/comment' => 'add-comment',
                        'GET <id:\d+>/history' => 'get-history'
                    ]
                ],
                'POST api/designer/projects/<projectId:\d+>/files' => 'api/designer/project/file/create',
                'GET api/designer/projects/<projectId:\d+>/files/<id>' => 'api/designer/project/file/download',
                'POST api/designer/projects/<projectId:\d+>/gears' => 'api/designer/project/gear/add',
                'DELETE api/designer/projects/<projectId:\d+>/gears/<id:\d+>' => 'api/designer/project/gear/remove',
                'GET api/designer/projects/<projectId:\d+>/gears' => 'api/designer/project/gear/index',
                'GET api/designer/projects/faq-info/<type:\d+>' => 'api/designer/project/default/faq-info',

                // Gear  module
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/admin/gears' => 'api/admin/gear/default'],
                    'extraPatterns' => [
                        'GET list' => 'get-list',
                        'GET producer-list' => 'get-producer-list'
                    ]
                ],
                'GET api/admin/gears/list' => 'api/admin/gear/default/get-list',
                'GET api/admin/gears/producer-list' => 'api/admin/gear/default/get-producer-list',
                'GET api/manager/gears/list' => 'api/manager/gear/default/get-list',
                'GET api/manager/gears/producer-list' => 'api/manager/gear/default/get-producer-list',
                'GET api/designer/gears/list' => 'api/designer/gear/default/get-list',
                'GET api/designer/gears/producer-list' => 'api/designer/gear/default/get-producer-list',
            ]
        ]
    ]
];

return $config;
