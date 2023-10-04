<?php
namespace app\modules\user\models\forms;

use Yii;
use yii\base\Model;
use kartik\password\StrengthValidator;
use app\components\interfaces\Form;
use app\modules\user\models\User;

/**
 * Password update form
 */
class PasswordForm extends Model implements Form {

    public $password;
    public $new_password;
    public $repeat_new_password;

    /**
     * User model associated with current form
     * @var User
     */
    private $_record;

    /**
     * Get user model
     * @return User
     */
    public function getRecord() {
        return $this->_record;
    }

    /**
     * Set user model
     * @param User $user
     */
    public function setRecord($record) {
        $this->_record = $record;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', 'new_password', 'repeat_new_password'], 'required'],
            ['password', 'validatePassword'],
            ['repeat_new_password', 'compare', 'compareAttribute'=>'new_password'],
            ['new_password', 'string', 'min' => 8],
            // ['new_password', 'match', 'pattern' => '/^(?=.*[A-Z].*[A-Z])(?=.*[!@#$&*])(?=.*[0-9].*[0-9])(?=.*[a-z].*[a-z].*[a-z]).{8}$/i'],
            // ['new_password', 'match', 'pattern' => '/^(?=.*[A-Z])(?=.*[0-9])(?=.*[a-z]).{8}$/i', 'message' => Yii::t('user', 'Password must contain at least one lowercase letter, one uppercase letter and one digit.')]
        ];
    }

    /**
     * Validates the password.
     *
     * @param string $attribute the attribute currently being validated
     */
    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            if (!Yii::$app->user->identity->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('user', 'Incorrect password.'));
            }
        }
    }

    /**
     * Saves new password
     * @return bool
     */
    public function save(): bool {
        $user = $this->record;
        if (!$this->validate() || !$user) {
            return false;
        }
        $user->setPassword($this->new_password);
        return $user->save();
    }

}
