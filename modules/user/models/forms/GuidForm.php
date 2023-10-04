<?php

namespace app\modules\user\models\forms;

use linslin\yii2\curl\Curl;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\HtmlPurifier;
use app\components\interfaces\Form;
use app\modules\user\models\{User, Comment};
use app\modules\user\helpers\UserHelper;

/**
 * User update form
 *
 * @property string $contact_guid
 * @property string $owner_guid
 */
class GuidForm extends Model
{
    public $owner_guid;
    public $contact_guid;
    public $one_c_status;

    /**
     * User model associated with current form
     * @var User
     */
    private $_record;

    /**
     * Get user model
     * @return User
     */
    public function getRecord()
    {
        return $this->_record;
    }

    /**
     * Set user model
     * @param User $record
     */
    public function setRecord($record)
    {
        $this->_record = $record;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->setAttributes($this->record->attributes);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contact_guid', 'owner_guid','one_c_status'], 'string', 'max' => 255],
            [['contact_guid', 'owner_guid'], 'trim'],
            [['contact_guid'], 'required'],
            [['contact_guid'], 'unique', 'targetClass' => User::class, 'message' => Yii::t('user', 'User with this guid is already registered.'), 'when' => function ($model, $attribute) {
                return $this->record->contact_guid != $model->contact_guid;
            }],
        ];
    }




    public function save()
    {
        $user = $this->record;
        if (!$this->validate() || !$user) {
            return false;
        }
        $user->scenario = User::SCENARIO_UPDATE;
        $user->setAttributes($this->attributes);
        if ($user->save()) {
            return true;
        }
        return false;
    }
}