<?php
namespace app\modules\user\models;
use app\modules\user\models\handlers\UserHandler;
use Yii;
use yii\base\Model;
use app\modules\user\models\User;
/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $email;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => User::class,
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => Yii::t('user', 'There is no user with this email address.')
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'email' => Yii::t('user', 'Email')
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public function preparePasswordReset()
    {
        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email' => $this->email,
        ]);
        if (!$user) {
            return false;
        }

        $user->handler->generateToken(Token::TYPE_PASSWORD_RESET);

        $url = Yii::$app->urlManager->createAbsoluteUrl(['auth/reset-password', 'email' => $this->email, 'token' => $user->passwordResetToken->value]);
        $user->handler->generateEvent(
            $this->email,
            UserHandler::EVENT_TYPE_KEY['passwordResetEvent'],
            $url,
            [
                'subject' => Yii::t('user', 'Password reset was requested.')
            ],
            $user->provider == 'itv' ? 'itv' : ''
        );

        return true;

//        return Yii::$app
//            ->mailer
//            ->compose([
//                'html' => 'passwordResetToken-html',
//                'text' => 'passwordResetToken-text'
//            ], [
//                'url' => Yii::$app->urlManager->createAbsoluteUrl(['auth/reset-password', 'email' => $this->email, 'token' => $user->passwordResetToken->value])
//            ]
//            )
//            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
//            ->setTo($this->email)
//            ->setSubject(Yii::t('user', 'Password reset was requested.'))
//            ->send();
    }
}
