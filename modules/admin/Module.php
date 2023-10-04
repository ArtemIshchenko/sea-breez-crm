<?php

namespace app\modules\admin;

use Yii;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;

/**
 * Customer base module definition class
 */
class Module extends \yii\base\Module
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Yii::$app->params['apiType'] = 'admin';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            // set access for administrators only
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function() {
                            return Yii::$app->user->isActive() && Yii::$app->user->isAdmin();
                        },
                        'denyCallback' => function() {
                            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
                        }
                    ]
                ]
            ]
        ];
    }
}
