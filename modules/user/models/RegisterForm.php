<?php

namespace app\modules\user\models;

use Yii;
use yii\base\Model;
use himiklab\yii2\recaptcha\ReCaptchaValidator;
use app\modules\user\events\RegistrationEvent;

class RegisterForm extends Model
{
    public $email;
    public $password;
    public $password_repeat;
    public $accept_conditions;
    public $reCaptcha;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('user', 'Email'),
            'password' => Yii::t('user', 'Password'),
            'password_repeat' => Yii::t('user', 'Repeat password')
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password', 'password_repeat'], 'required'],
            [['email'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],
            [['email'], 'email'],
            [['email'], 'unique', 'targetClass' => User::class, 'message' => Yii::t('user', 'User with this email is already registered.')],
            [['password'], 'string', 'min' => 8],
            [['password_repeat'], 'compare', 'compareAttribute' => 'password'],
            [['accept_conditions'], 'required', 'requiredValue' => 1, 'message' => Yii::t('user', 'To proceed registration conditions must be accepted.')],
            [['reCaptcha'], ReCaptchaValidator::class, 'uncheckedMessage' => Yii::t('user', 'To proceed registration prove you are not a robot.')]
        ];
    }

    /**
     * Creates new user
     * @return Boolean
     */
    public function register() {
        if ($this->validate()) {
            $model = new User();
            $model->scenario = User::SCENARIO_REGISTER;
            $model->setAttributes($this->attributes);
            return $model->handler->create() ? $model : false;
        }
        return false;
    }
}
