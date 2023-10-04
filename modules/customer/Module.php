<?php

namespace app\modules\customer;

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

        // Yii::$app->params['apiType'] = 'customer';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            // set access for authenticated users only
            // more detailed access rules may be set in submodules and controllers
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function() {
                            return Yii::$app->user->isActive() && Yii::$app->user->isCustomer();
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
