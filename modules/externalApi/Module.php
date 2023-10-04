<?php

namespace app\modules\externalApi;

use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

/**
 * Designer base module definition class
 */
class Module extends \yii\base\Module
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Yii::$app->params['isApi'] = true;
        Yii::$app->params['external'] = true;
        Yii::$app->request->parsers['application/json'] = 'yii\web\JsonParser';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            // set response format
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON
                ]
            ],
            // more detailed access rules may be set in submodules and controllers
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true
                    ]
                ],
                'denyCallback' => function() {
                    throw new UnauthorizedHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
                }
            ],
        ];
    }
}
