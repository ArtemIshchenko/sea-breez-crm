<?php
namespace app\modules\user\models\forms;

use Yii;
use yii\base\Model;
use app\components\interfaces\Form;
use app\modules\user\models\User;

/**
 * Lang update form
 */
class LangForm extends Model implements Form {

    public $lang;

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
            [['lang'], 'required'],
            [['lang'], 'string', 'max' => 3],
        ];
    }

    /**
     * Saves new lang
     * @return bool
     */
    public function save(): bool {
        $user = $this->record;
        if (!$this->validate() || !$user) {
            return false;
        }
        $user->lang = $this->lang;
        return $user->save();
    }

}
