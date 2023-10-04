<?php

namespace app\modules\main;

use Yii;
use yii\base\BootstrapInterface;
use app\modules\main\models\Content;

/**
 * user module definition class
 */
class Module extends \yii\base\Module// implements BootstrapInterface
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
    }

    // Rules moved to config file for better performance
    // /**
    //  * @inheritdoc
    //  */
    // public function bootstrap($app)
    // {
    //     // $app->getUrlManager()->addRules([
    //     //     [
    //     //         'class' => 'yii\web\UrlRule',
    //     //         'pattern' => '/',
    //     //         'route' => 'main/default/index',
    //     //         'verb' => 'GET'
    //     //     ]
    //     // ], false);
    // }
}
