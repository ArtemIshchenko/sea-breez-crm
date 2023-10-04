<?php

namespace app\modules\project\controllers\api;

use Yii;
use yii\web\ServerErrorHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\data\ActiveDataProvider;
use app\components\helpers\ErrorHelper;
use app\modules\project\models\forms\{CommentForm};
use app\modules\project\models\{Project, History};

class BaseDefaultController extends \app\modules\api\components\controllers\ActiveController
{

    public $modelClass = 'app\modules\project\models\Project';

    /**
     * Update status action.
     * @param  int $id Project's id
     * @return Project
     */
    public function actionUpdateStatus($id) {
        $project = Project::findOne($id);
        if (!$project) {
            throw new NotFoundHttpException(Yii::t('project', 'Project not found.'));
        }
        $this->checkAccess('update-status', $project);
        $newStatus = Yii::$app->request->getBodyParam('status');
        if (!$newStatus) {
            throw new BadRequestHttpException(Yii::t('app', 'New status must be specified.'));
        }
        $newStatus = array_search($newStatus, Project::statuses());
        if (!$newStatus) {
            throw new BadRequestHttpException(Yii::t('app', 'Unknown status.'));
        }
        if ($project->handler->canUpdateStatus($newStatus)&& $project->handler->updateStatus($newStatus)) {
            return $project;
        }
        throw new ServerErrorHttpException(Yii::t('app', 'Saving new status failed.'));
    }

    /**
     * Update status action.
     * @param  int $id Project's id
     * @return Project
     */
    public function actionUpdateOnec($id) {
        $project = Project::findOne($id);
        if (!$project) {
            throw new NotFoundHttpException(Yii::t('project', 'Project not found.'));
        }
        $response = $project->handler->sendProjectTo1C();
        $project->handler->sendSpecDataToParser();
        if ($response) {
            return $response;
        }
        throw new ServerErrorHttpException(Yii::t('app', 'Saving new status failed.'));
    }

    /**
     * Returns history of project
     * @param  integer $id Project ID
     * @return ActiveDataProvider
     */
    public function actionGetHistory($id) {
        $project = Project::findOne($id);
        if (!$project) {
            throw new NotFoundHttpException();
        }
        $this->checkAccess('get-history', $project);

        $requestParams = Yii::$app->request->get();
        $query = History::find()->where(['project_id' => $project->id])->orderBy(['id' => SORT_DESC]);
        return Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $query,
            'pagination' => [
                'params' => $requestParams,
            ]
        ]);
    }

    /**
     * Returns FAQ info of project
     * @return array
     */
    public function actionFaqInfo($type=1) {

        $faqInfo = '';
        $faqInfoFile = '';
        switch ($type) {
            case 1:
                $faqInfoFile = Yii::getAlias('@app') . '/web/files/terms_of_use_clients.txt';
                break;
            case 2:
                $faqInfoFile = Yii::getAlias('@app') . '/web/files/terms_of_use_employes.txt';
                break;
        }

        if (file_exists($faqInfoFile)) {
            $faqInfo = file_get_contents($faqInfoFile);
        }

        return ['text' => $faqInfo];
    }

    /**
     * Adds comment to project.
     * @param int $id Project's id.
     * @return Project|CommentForm Project model or comment form model in case of validation errors.
     */
    public function actionAddComment($id) {
        $project = Project::findOne($id);
        if (!$project) {
            throw new NotFoundHttpException(Yii::t('project', 'Project not found.'));
        }
        $this->checkAccess('add-comment', $project);
        $form = new CommentForm([
            'record' => $project
        ]);
        if (!$form->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            ErrorHelper::throwInputNotLoaded();
        }
        if ($form->save()) {
            return $project;
        }
        ErrorHelper::checkModelHasErrors($form);
        return $form;
    }

    /**
     * Change project designer
     * @param int $designer_id
     * @param int $designing_deadline
     * @return Project
     */
    public function actionSetDesigner($id) {
        $project = Project::findOne($id);
        if (!$project) {
            throw new NotFoundHttpException(Yii::t('project', 'Project not found.'));
        }
        if ($project->status != Project::STATUS_DESIGNING) {
            throw new UnprocessableEntityHttpException((Yii::t('project', 'Designer change is possible for projects with "designing" status')));
        }
        $this->checkAccess('add-comment', $project);

        $designerId = (int) Yii::$app->request->getBodyParam('designer_id');
        $deadline = (int) Yii::$app->request->getBodyParam('designing_deadline');
        if ($project->handler->setDesigner($designerId, $deadline)) {
            return $project;
        }
        throw new ServerErrorHttpException(Yii::t('project', 'Saving new designer failed.'));
    }

}
