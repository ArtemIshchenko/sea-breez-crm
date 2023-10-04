<?php

namespace app\modules\project\controllers\api\designer;

use Yii;
use yii\web\NotFoundHttpException;
use app\modules\project\models\Project;

class GearController extends \app\modules\project\controllers\api\BaseGearController
{
    /**
     * {@inheritdoc}
     */
    protected function findProject($id) {
        $project = Project::findOne([
            'id' => $id
        ]);
        if (!$project || ($project->designer_id != Yii::$app->user->id)) {
            throw new NotFoundHttpException(Yii::t('project', 'Project not found.'));
        }
        return $project;
    }
}
