<?php

namespace app\modules\project\controllers\api\customer;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\modules\project\models\Project;
use app\modules\project\models\forms\ProjectForm;
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
        $actions['create']['formModel'] = ProjectForm::class;
        $actions['create']['scenario'] = ProjectForm::SCENARIO_CREATE;

        // Update action
        $actions['update']['formModel'] = ProjectForm::class;
        $actions['update']['scenario'] = ProjectForm::SCENARIO_UPDATE;

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAccess($action, $model = null, $params = []) {
        if ($model && $model->user_id != Yii::$app->user->id) {
            throw new ForbiddenHttpException(Yii::t('project', 'You are not allowed to access this project.'));
        }
        if ($model && $action == 'delete' && $model->status != Project::STATUS_CREATED) {
            throw new ForbiddenHttpException(Yii::t('project', 'It is not possible to delete already sent project.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function actionAddComment($id) {
        throw new NotFoundHttpException();
    }

    /**
     * {@inheritdoc}
     */
    public function actionSetDesigner($id) {
        throw new NotFoundHttpException();
    }

}
