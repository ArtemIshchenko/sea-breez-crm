<?php
namespace app\modules\user\models\forms;

use Yii;
use yii\base\Model;
use app\components\interfaces\Form;
use app\modules\user\models\User;
use app\modules\user\events\ManagerEvent;

/**
 * Customer's manager update form
 */
class ManagerForm extends Model implements Form {

    public $manager_id;

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
            [['manager_id'], 'required'],
            [
                ['manager_id'],
                'exist',
                'targetClass' => User::class,
                'targetAttribute' => 'id',
                'filter' => ['role' => User::ROLE_MANAGER, 'status' => User::STATUS_ACTIVE],
                'message' => Yii::t('user', 'Manager not found.')
            ]
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
        return $user->handler->updateManager($this->manager_id);
    }

}
