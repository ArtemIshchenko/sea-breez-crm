<?php

namespace app\modules\main\controllers;

use Yii;
use yii\web\ErrorAction;
use app\components\controllers\Controller;

class DefaultController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
               'class' => ErrorAction::class
            ]
        ];
    }

    /**
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        return $this->redirect(['/user/auth/login']);
    }
}
