<?php

namespace app\modules\user\controllers\api;

use Yii;
use yii\filters\ContentNegotiator;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\web\UnauthorizedHttpException;
use yii\web\Cookie;
use yii\filters\Cors;

class AuthController extends \app\modules\api\components\controllers\Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors['verb'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'init'  => ['GET'],
                'logout'   => ['POST']
            ]
        ];

        return $behaviors;
    }

    /**
     * Return user identity details if authenticated
     * and generate csrf cookie if not set
     */
    public function actionInit() {
        return [
            'identity' => Yii::$app->user->identity,
            'csrfToken' => Yii::$app->request->getCsrfToken(),
            'googleApiKey' => Yii::$app->params['google_api_key']
        ];
    }

    /**
     * Logout user from aplication
     */
    public function actionLogout() {

        return Yii::$app->user->logout();

    }

}
