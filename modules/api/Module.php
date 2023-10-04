<?php

namespace app\modules\api;

use Yii;
use yii\web\Response;
use yii\filters\ContentNegotiator;
use yii\filters\AccessControl;
use yii\web\UnauthorizedHttpException;

/**
 * Api base module definition class
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

        // set json parser for api requests
        Yii::$app->request->parsers['application/json'] = 'yii\web\JsonParser';

        // If api language is different from application language
        // Yii::$app->language = 'en-US';

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

             // set access for authenticated users only
             // more detailed access rules may be set in submodules and controllers
             'access' => [
                 'class' => AccessControl::class,
                 'rules' => [
                     [
                         'allow' => true,
                         'roles' => ['@']
                     ],
                 ],
                 'denyCallback' => function() {
                     throw new UnauthorizedHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
                 }
             ]
         ];
     }
}
