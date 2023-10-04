<?php

namespace app\modules\user\controllers;

use Yii;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use app\modules\user\models\LoginForm;
use app\modules\user\models\RegisterForm;
use app\modules\user\models\PasswordResetRequestForm;
use app\modules\user\models\ResetPasswordForm;
use app\modules\user\models\User;
use app\modules\user\models\Token;
use app\modules\user\models\UserApplication;
use app\modules\user\helpers\UserHelper;
use app\modules\user\events\ConfirmationEvent;

/**
 * Auth controller for the `user` module
 */
class AuthController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'login', 'register', 'confirm-registration', 'request-password-reset', 'reset-password'],
                        'allow' => true,
                    ]
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction ($action) {
        if (Yii::$app->user->isActive()) {
            $this->redirect(UserHelper::getAppUrl(), 303);
            return false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Default auth action
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->redirect(['auth/login']);
    }

    /**
     * Login action.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $this->redirect(UserHelper::getAppUrl(), 303);
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Register action
     * @return string
     */
    public function actionRegister() {
        $model = new RegisterForm();
        $registered = false;
        if ($model->load(Yii::$app->request->post())) {
            try {
                if ($model->register()) {
                    Yii::$app->session->setFlash('success', Yii::t('user', 'You were successfully registered. Confirmation letter was sent to your email.'));
                    $registered = true;
                }
                else if (!$model->hasErrors()) {
                    Yii::$app->session->setFlash('error', implode(' ', [
                        Yii::t('user', 'You were not registered for unknown reason.'),
                        Yii::t('app', 'Please contact administrator to resolve this issue.')
                    ]));
                }
            }
            catch (\Exception $e) {
                Yii::$app->session->setFlash('error', implode(' ', [
                    $e->getMessage(),
                    Yii::t('app', 'Please contact administrator to resolve this issue.')
                ]));
            }
        }

        if($registered){
            return $this->render('register-success', [
                'model' => $model,
            ]);
        }
        $termsOfUse = '';
        $termsOfUseFile = Yii::getAlias('@app') . '/web/files/terms_of_use_clients.txt';
        if (file_exists($termsOfUseFile)) {
            $termsOfUse = file_get_contents($termsOfUseFile);
        }

        return $this->render('register', [
            'model' => $model,
            'termsOfUse' => $termsOfUse,
        ]);
    }

    /**
     * Confirms user registration.
     * @param  string $email
     * @param  string $token
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionConfirmRegistration($email, $token) {
        $user = User::findByEmail($email);
        if ($user && $user->handler->validateToken($token, Token::TYPE_CONFIRMATION) && $user->handler->confirmRegistration()) {
            return $this->redirect(UserHelper::getAppUrl(), 303);
        }
        if ($user && $user->status != User::STATUS_REGISTERED) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Your email is already confirmed. You can login now.'));
            return $this->redirect('login');
        }
        throw new NotFoundHttpException();
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->preparePasswordReset()) {
                Yii::$app->session->setFlash('success', Yii::t('user', 'A letter with password reset instructions was sent to your email.'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('user', 'Sorry, we are unable to reset password for the provided email address.'));
            }
        }
        return $this->render('requestPasswordReset', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $email
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($email, $token)
    {
        $user = User::findByEmail($email);
        if (!$user || !$user->handler->validateToken($token, Token::TYPE_PASSWORD_RESET)) {
            throw new NotFoundHttpException();
        }

        $model = new ResetPasswordForm([
            'user' => $user
        ]);
        $saved = false;
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            $saved = true;
            Yii::$app->session->setFlash('success', Yii::t('user', 'New password saved. You can <a class="alert-link" href="{url}">sign in</a> using your new password.', ['url' => Url::to(['auth/login'])]));
        }
        return $this->render('resetPassword', [
            'model' => $model,
            'saved' => $saved
        ]);
    }

}
