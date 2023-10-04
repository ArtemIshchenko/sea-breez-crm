<?php

namespace app\modules\api\components\controllers;

use Yii;

class ActiveController extends \yii\rest\ActiveController
{

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = true;

    /**
     * @inheritdoc
     */
    public function behaviors() {
        $behaviors = parent::behaviors();

        // use web access filter specified in modules instead of authenticator
        if (isset($behaviors['authenticator']))
            unset($behaviors['authenticator']);

        // Response format already set in `Api` module
        if (isset($behaviors['contentNegotiator']))
           unset($behaviors['contentNegotiator']);

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions  = parent::actions();
        $actions['view']['class'] = 'app\modules\api\components\actions\ViewAction';
        $actions['delete']['class'] = 'app\modules\api\components\actions\DeleteAction';
        $actions['index']['class'] = 'app\modules\api\components\actions\IndexAction';
        $actions['update']['class'] = 'app\modules\api\components\actions\UpdateAction';
        $actions['create']['class'] = 'app\modules\api\components\actions\CreateAction';
        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction ($action) {
        if (isset(Yii::$app->user)) {
            $langMap = [
                'uk' => 'uk-UA',
                'ru' => 'ru-RU',
            ];
            if (isset($langMap[Yii::$app->user->identity->lang])) {
                Yii::$app->language = $langMap[Yii::$app->user->identity->lang];
            }
        }

        return parent::beforeAction($action);
    }
}
