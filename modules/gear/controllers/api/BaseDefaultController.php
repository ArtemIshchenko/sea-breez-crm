<?php

namespace app\modules\gear\controllers\api;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\data\ActiveDataProvider;
use app\modules\gear\models\Gear;

class BaseDefaultController extends \app\modules\api\components\controllers\ActiveController
{

    public $modelClass = 'app\modules\gear\models\Gear';

    public function actionGetProducerList() {
        return Yii::$app->params['gearProducers'];
    }

    public function actionGetList() {
        $gears = Gear::find()->select(['id', 'title'])->all();
        return ArrayHelper::map($gears, 'id', 'title');
    }

}
