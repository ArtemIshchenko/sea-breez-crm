<?php

namespace app\modules\user\controllers\api\admin;

use app\modules\user\helpers\UserHelper;
use app\modules\user\models\handlers\UserHandler;
use app\modules\user\models\History;
use app\modules\user\models\ResetPasswordForm;
use linslin\yii2\curl\Curl;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use yii\helpers\ArrayHelper;
use app\components\helpers\ErrorHelper;
use app\modules\user\models\User;
use app\modules\user\models\forms\{GuidForm, UserForm, CommentForm, ManagerForm};
use app\modules\user\models\searches\{CustomerSearch, ManagerSearch, DesignerSearch, AdministratorSearch};
// use app\modules\user\models\UserHistory;

class DefaultController extends \app\modules\api\components\controllers\ActiveController
{

    public $modelClass = 'app\modules\user\models\User';
    /**
     * @inheritdoc
     */
    public function actions() {
        $actions = parent::actions();

        // Index action
        $actions['index']['searchModel'] = function() {
            $role = Yii::$app->request->get('role');
            if (!$role) {
                throw new BadRequestHttpException(Yii::t('user', 'User role should be specified to perform search.'));
            }
            $role = array_search($role, User::roles());
            if (!$role) {
                throw new BadRequestHttpException(Yii::t('user', 'User role not found.'));
            }
            switch ($role) {
                case User::ROLE_CUSTOMER:
                    return CustomerSearch::class;
                case User::ROLE_MANAGER:
                    return ManagerSearch::class;
                case User::ROLE_DESIGNER:
                    return DesignerSearch::class;
                case User::ROLE_ADMIN:
                    return AdministratorSearch::class;
            }
        };

        // Update action
        $actions['update']['formModel'] = UserForm::class;
        $actions['update']['scenario'] = UserForm::SCENARIO_ADMIN;

        unset($actions['create']);
        unset($actions['delete']);

        return $actions;
    }

    /**
     * Update status action
     * @param $id
     * @return User
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionUpdateStatus($id): User
    {
        $user = User::find()
            ->where(['id' => $id])
            ->andWhere(['not', ['status' => User::STATUS_REGISTERED]])
            ->one();
        if (!$user) {
            throw new NotFoundHttpException(Yii::t('user', 'User not found.'));
        }
        $this->checkAccess('update-status', $user);
        $newStatus = Yii::$app->request->getBodyParam('status');
        if (!$newStatus) {
            throw new BadRequestHttpException(Yii::t('app', 'New status must be specified.'));
        }
        $newStatus = array_search($newStatus, User::statuses());
        if (!$newStatus) {
            throw new BadRequestHttpException(Yii::t('app', 'Unknown status.'));
        }
        if (!$user->handler->updateStatus($newStatus)) {
            throw new ServerErrorHttpException(Yii::t('app', 'Saving new status failed.'));
        }
        return $user;
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
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException(Yii::t('user', 'User not found.'));
        }
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
     * Assign a new password to user action.
     * @param int $id User's id.
     * @return User|ResetPasswordForm User model or ResetPasswordForm model in case of validation errors.
     */
    public function actionAssignNewPassword($id) {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException(Yii::t('user', 'User not found.'));
        }
        $this->checkAccess('assign-new-password', $user);
        $form = new ResetPasswordForm([
            'user' => $user
        ]);
        if (!$form->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            ErrorHelper::throwInputNotLoaded();
        }
        if ($form->validate() && $form->resetPassword()) {
            $user = User::findOne($id);
            $user->temporary_pass = $form->new_password;
            $user->save();
            return $user;
        }
        ErrorHelper::checkModelHasErrors($form);
        return $form;
    }

    /**
     * Assign a new password to user action.
     * @param int $id User's id.
     * @return User|ResetPasswordForm User model or ResetPasswordForm model in case of validation errors.
     */
    public function actionSendNewPassword($id) {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException(Yii::t('user', 'User not found.'));
        }
        $this->checkAccess('send-new-password', $user);

        $user->handler->generateEvent(
            $user->email,
            UserHandler::EVENT_TYPE_KEY['newUserPasswordSent'],
            '',
            [
                'newPassword' => $user->temporary_pass,
                'subject' => Yii::t('user', 'Password was reset.'),
            ],
            $user->provider == 'itv' ? 'itv' : ''
        );

        $user->temporary_pass = '';
        $user->save();
        return $user;
    }

    /**
     * Set manager to cutomer action.
     * @param  int $id User's id
     * @return User|ManagerForm User model or form model in case of validation errors.
     */
    public function actionSetManager($id)
    {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException(Yii::t('user', 'User not found.'));
        }
        $this->checkAccess('set-manager', $user);
        if ($user->role != User::ROLE_CUSTOMER) {
            throw new BadRequestHttpException(Yii::t('user', 'You can set manager only to customers.'));
        }
        $form = new ManagerForm([
            'record' => $user
        ]);
        if (!$form->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            ErrorHelper::throwInputNotLoaded();
        }
        if ($form->save()) {
            return $form->record;
        }
        ErrorHelper::checkModelHasErrors($form);
        return $form;
    }

    /**
     * Retrieve list of members by role
     * @param string|array $role User role label.
     * @param boolean $active If query users with status `active` only.
     * @return array
     */
    public function actionGetList() {
        $role = Yii::$app->request->get('role');
        $active = Yii::$app->request->get('active', false);
        if (!$role) {
            throw new BadRequestHttpException(Yii::t('user', 'Unknown user role.'));
        }
        $this->checkAccess('get-list');
        $roles = (array) $role;

        $i = 0;
        foreach ($roles as $r) {
            $roles[$i] = array_search($r, User::roles());
            if (!$roles[$i]) {
                throw new BadRequestHttpException(Yii::t('user', 'Unknown user role.'));
            }
            $i++;
        }

        $query = User::find()
            ->select(['id', 'first_name', 'last_name', 'role'])
            ->where(['role' => $roles])
            ->asArray();
        if ($active) {
            $query->andWhere(['status' => User::STATUS_ACTIVE]);
        }
        $models = $query->all();
        return ArrayHelper::map($models, 'id', function($model) {
            return trim($model['last_name'] . ' ' . $model['first_name']);
        }, (count($roles) === 1) ? null : function($model) {
            return User::roles()[$model['role']];
        });
    }

    /**
     * @param $id
     * @return object
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetHistory($id) {

        $this->checkAccess('get-history');
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException();
        }

        $requestParams = Yii::$app->request->get();
        $query = History::find()->where(['user_id' => $user->id])->orderBy(['id' => SORT_DESC]);

        return Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $query,
            'pagination' => [
                'params' => $requestParams,
            ]
        ]);
    }
}
