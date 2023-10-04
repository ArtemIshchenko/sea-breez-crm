<?php

namespace app\modules\user\controllers\api\designer;

use Yii;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use app\modules\user\models\User;

class DefaultController extends \app\modules\api\components\controllers\Controller
{

    public function actionView($id) {
        $model = User::find()
            ->where(['{{%user}}.id' => $id])
            ->joinWith('projects')
            ->andWhere(['{{%project}}.designer_id' => Yii::$app->user->id])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException(Yii::t('user', 'Client not found.'));
        }
        return $model;
    }
}
