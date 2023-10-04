<?php

namespace app\modules\manager;

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

        // Yii::$app->params['apiType'] = 'manager';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            // set access for managers only
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function() {
                            return Yii::$app->user->isActive() && Yii::$app->user->isManager();
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
