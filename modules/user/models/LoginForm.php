<?php
namespace app\modules\user\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $email;
    public $password;
    public $remember_me = false;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            [['email'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],
            ['remember_me', 'boolean'],
            ['password', 'validatePassword'],
            ['email', 'validateStatus']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'email' => Yii::t('user', 'Email'),
            'password' => Yii::t('user', 'Password'),
            'remember_me' => Yii::t('user', 'Remember me')
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('user', 'Incorrect email or password.'));
            }
        }
    }

    /**
     * Checks if user's status is other than 'active'
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateStatus($attribute, $params) {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if ($user->status == User::STATUS_REGISTERED) {
                $this->addError($attribute, Yii::t('user', 'This user is registered but not activated yet. Please check your email for activation letter.'));
            }
            if ($user->status == User::STATUS_SUSPENDED) {
                $this->addError($attribute, Yii::t('user', 'This user is suspended.'));
            }
        }
    }

    /**
     * Logs in a user using the provided email and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return $this->user->handler->login($this->remember_me ? Yii::$app->params['user.rememberMeDuration'] : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[email]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByEmail($this->email);
        }

        return $this->_user;
    }
}
