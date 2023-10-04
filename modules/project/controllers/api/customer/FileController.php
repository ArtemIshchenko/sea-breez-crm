<?php

namespace app\modules\project\controllers\api\customer;

use Yii;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use app\components\helpers\ErrorHelper;
use app\modules\project\models\{Project, File};
use app\modules\project\models\forms\FileForm;

class FileController extends \app\modules\project\controllers\api\BaseFileController
{
    /**
     * {@inheritdoc}
     */
    protected function findProject($id) {
        $project = Project::findOne([
            'id' => $id,
            'user_id' => Yii::$app->user->id
        ]);
        if (!$project) {
            throw new NotFoundHttpException(Yii::t('project', 'Project not found.'));
        }
        return $project;
    }
}
