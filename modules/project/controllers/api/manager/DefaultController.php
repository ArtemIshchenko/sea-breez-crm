<?php

namespace app\modules\project\controllers\api\manager;

use Yii;
use app\modules\project\models\Project;
use app\modules\project\models\forms\{ProjectForm};
use app\modules\project\models\searches\ProjectSearch;

class DefaultController extends \app\modules\project\controllers\api\BaseDefaultController
{

    /**
     * @inheritdoc
     */
    public function actions() {
        $actions = parent::actions();

        // Index action
        $actions['index']['searchModel'] = ProjectSearch::class;

        // Create action
        unset($actions['create']);

        // Update action
        $actions['update']['formModel'] = ProjectForm::class;
        $actions['update']['scenario'] = ProjectForm::SCENARIO_UPDATE;

        // Delete action
        unset($actions['delete']);

        return $actions;
    }


    /**
     * {@inheritdoc}
     */
    public function checkAccess($action, $model = null, $params = []) {
        if ($model && (!$model->author->manager_id || $model->author->manager_id != Yii::$app->user->id)) {
            throw new ForbiddenHttpException(Yii::t('project', 'You are not allowed to access this project.'));
        }
    }

}
