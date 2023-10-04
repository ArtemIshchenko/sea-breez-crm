<?php
namespace app\modules\user\models\forms;

use Yii;
use yii\base\Model;
use app\components\interfaces\Form;
use app\modules\user\models\User;
use app\modules\user\events\EmailEvent;

/**
 * Email update form
 */
class EmailForm extends Model implements Form {

    public $email;
    private $_oldEmail;

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
        $this->_oldEmail = $record->email;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email'], 'required'],
            [['email'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['email'], 'unique', 'targetClass' => User::class, 'message' => Yii::t('user', 'User with this email is already registered.')]
        ];
    }

    /**
     * Saves new email
     * @return bool
     */
    public function save(): bool {
        $user = $this->record;
        if (!$this->validate() || !$user) {
            return false;
        }
        return $user->handler->updateEmail($this->email);
    }

}
