<?php

namespace app\modules\user\controllers\api\manager;

use linslin\yii2\curl\Curl;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use yii\helpers\ArrayHelper;
use app\components\helpers\ErrorHelper;
use app\modules\user\models\User;
use app\modules\user\models\forms\{GuidForm, UserForm, CommentForm};
use app\modules\user\models\searches\CustomerSearch;

class DefaultController extends \app\modules\api\components\controllers\ActiveController
{

    public $modelClass = 'app\modules\user\models\User';

    /**
     * @inheritdoc
     */
    public function actions() {
        $actions = parent::actions();

        // Index action
        $actions['index']['searchModel'] = CustomerSearch::class;

        // Update action
        $actions['update']['formModel'] = UserForm::class;
        $actions['update']['scenario'] = UserForm::SCENARIO_MANAGER;
        $actions['update']['findModel'] = [$this, 'findModel'];
        
        // View action
        $actions['view']['findModel'] = [$this, 'findModel'];

        unset($actions['create']);
        unset($actions['delete']);

        return $actions;
    }

    public function findModel($id, $action) {
        $model = User::find()->where(['id' => $id, 'manager_id' => Yii::$app->user->id])->one();
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('user', 'User not found.'));
        }
        return $model;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function actionCheckGuid()
    {
        $guid = Yii::$app->getRequest()->getBodyParam('guid');
        $link = Yii::$app->getRequest()->getBodyParam('link');
        $link = $link ? $link: Yii::$app->params['ONE_C_INTEGRATION']['customer_url'];
        $curl = new Curl();
        $data = [
            "request_key" => Yii::$app->params['ONE_C_INTEGRATION']['request_key'],
            "request_type" => "GET",
            "url" => $link.$guid
        ];
        $response = $curl
            ->setRawPostData(json_encode($data))
            ->setHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => Yii::$app->params['ONE_C_INTEGRATION']['Authorization'],
                'Client-Id' => Yii::$app->params['ONE_C_INTEGRATION']['client_id']
            ])
            ->post(Yii::$app->params['ONE_C_INTEGRATION']['proxy']);
        if($response){
            return [
                'success' => 'success',
                'data' => json_decode($response)
            ];
        }else{
            return [
                'success' => 'error',
                'data' => $response
            ];
        }

    }


    public function actionStore1c($id)
    {
        $model = User::findOne($id);
        $form = new GuidForm([
            'record' => $model
        ]);
        if (!$form->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            ErrorHelper::throwInputNotLoaded();
        }
        if (!$form->save()) {
            ErrorHelper::checkModelHasErrors($form);
            return $form;
        }
        return $form;
    }
    /**
     * Add comment to user action.
     * @param int $id User's id.
     * @return User|CommentForm User model or comment form model in case of validation errors.
     */
    public function actionAddComment($id) {
        $user = call_user_func([$this, 'findModel'], $id, 'add-comment');
        $this->checkAccess('add-comment', $user);
        $form = new CommentForm([
            'record' => $user
        ]);
        if (!$form->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            ErrorHelper::throwInputNotLoaded();
        }
        if ($form->save()) {
            $user = $form->record;
            $user->scenario = User::SCENARIO_VIEW;
            return $user;
        }
        ErrorHelper::checkModelHasErrors($form);
        return $form;
    }

    /**
     * Retrieve list of members by role
     * @param string $role User role label.
     * @param boolean $active If query users with status `active` only.
     * @return array
     */
    public function actionGetList($role, $active = true) {
        $this->checkAccess('get-list');
        $role = array_search($role, User::roles());
        if (!$role || !in_array($role, [User::ROLE_DESIGNER, User::ROLE_CUSTOMER])) {
            throw new BadRequestHttpException(Yii::t('user', 'Unknown user role.'));
        }
        $query = User::find()
            ->select(['id', 'first_name', 'last_name'])
            ->where(['role' => $role])
            ->asArray();
        if ($active) {
            $query->andWhere(['status' => User::STATUS_ACTIVE]);
        }
        if ($role == User::ROLE_CUSTOMER) {
            $query->andWhere(['manager_id' => Yii::$app->user->id]);
        }
        $models = $query->all();
        return ArrayHelper::map($models, 'id', function($model) {
            return trim($model['last_name'] . ' ' . $model['first_name']);
        });
    }
}
