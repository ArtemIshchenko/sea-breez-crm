<?php

namespace app\modules\project\controllers\api;

use Yii;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnprocessableEntityHttpException;
use app\components\helpers\ErrorHelper;
use app\modules\project\models\{Project};
use app\modules\gear\models\Gear;

class BaseGearController extends \app\modules\api\components\controllers\Controller
{

    /**
     * Finds project
     * @param int $id
     */
    protected function findProject($id) {
        throw new ServerErrorHttpException('findProject method has to be overwritten.');
    }

    /**
     * Uploads new files
     * @param int $projectId
     * @param string $type
     * @return Project|FileForm
     */
    public function actionAdd($projectId) {
        $project = $this->findProject($projectId);
        $gearId = Yii::$app->request->post('id');
        $gear = Gear::findOne($gearId);
        if (!$gear) {
            throw new UnprocessableEntityHttpException(Yii::t('gear', 'Gear is not found.'));
        }
        $project->handler->addGear($gear);
        return $gear;
    }

    /**
     * Downloads file
     * @param int $projectId
     * @param string $filename
     * @return \yii\web\Response
     */
    public function actionIndex($projectId) {
        $project = $this->findProject($projectId);
        return $project->gears;
    }

    /**
     * Removes file
     * @param int $projectId
     * @param string $filename
     * @return Project
     */
    public function actionRemove($projectId, $id) {
        $project = $this->findProject($projectId);
        $gear = Gear::findOne($id);
        if (!$gear) {
            throw new UnprocessableEntityHttpException(Yii::t('gear', 'Gear is not found.'));
        }
        $project->handler->removeGear($gear);

        Yii::$app->getResponse()->setStatusCode(204);
    }


}
