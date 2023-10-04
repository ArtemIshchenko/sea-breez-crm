<?php

namespace app\modules\project\controllers\api\admin;

use Yii;
use yii\web\NotFoundHttpException;
use app\modules\project\models\Project;

class FileController extends \app\modules\project\controllers\api\BaseFileController
{
    /**
     * {@inheritdoc}
     */
    protected function findProject($id) {
        $project = Project::findOne([
            'id' => $id
        ]);
        if (!$project) {
            throw new NotFoundHttpException(Yii::t('project', 'Project not found.'));
        }
        return $project;
    }
}
