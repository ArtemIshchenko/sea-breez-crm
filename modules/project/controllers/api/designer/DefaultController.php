<?php

namespace app\modules\project\controllers\api\designer;

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
        unset($actions['update']);

        // Delete action
        unset($actions['delete']);

        return $actions;
    }


    /**
     * {@inheritdoc}
     */
    public function checkAccess($action, $model = null, $params = []) {
        if ($model && (!$model->designer_id || $model->designer_id != Yii::$app->user->id)) {
            throw new ForbiddenHttpException(Yii::t('project', 'You are not allowed to access this project.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function actionSetDesigner($id) {
        throw new NotFoundHttpException();
    }

}
