<?php

namespace app\modules\user\models;

use app\modules\user\models\History;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;
use yii\helpers\Url;
use yii\helpers\HtmlPurifier;
use yii\db\ActiveRecord;
use app\modules\user\events\Handler;
use app\modules\user\helpers\UserHelper;
use app\modules\user\models\decorators\UserDecorator;
use app\modules\user\models\handlers\UserHandler;
use app\components\interfaces\{Handled, Decorated};
use app\modules\project\models\Project;

/**
 * User model
 *
 * @property integer $id
 * @property string $auth_key
 * @property string $password_hash
 * @property string $email
 * @property integer $manager_id
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property string $company
 * @property string $business_type
 * @property string $business_id
 * @property string $phone
 * @property string $mobile_phone
 * @property string $address
 * @property integer $temporary_pass
 * @property integer $lang
 * @property string $website
 * @property integer $role
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $entered_at
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface, Handled, Decorated
{
    const STATUS_REGISTERED = 10;
    const STATUS_ACTIVE = 20;
    const STATUS_SUSPENDED = 30;

    const ROLE_CUSTOMER = 1;
    const ROLE_MANAGER = 2;
    const ROLE_DESIGNER = 3;
    const ROLE_ADMIN = 4;
    const ROLE_1C = 10;

    const SCENARIO_DEFAULT = 'default';
    const SCENARIO_VIEW = 'view';
    const SCENARIO_REGISTER = 'register';
    const SCENARIO_ADMIN = 'admin';
    const SCENARIO_MANAGER = 'manager';
    const SCENARIO_UPDATE = 'update';


    const ONE_C_STATUS_EXIST = 'success';
    const ONE_C_STATUS_MODERATION = 'moderation';
    const ONE_C_STATUS_ERROR = 'error';

    const LANG = [
        'uk' => 'uk',
        'ru' => 'ru',
    ];

    /**
     * Handler object containing AR business logic.
     * @var UserHandler
     */
    private $_handler;

    /**
     * Handler object containing AR decorating logic.
     * @var UserDecorator
     */
    private $_decorator;

    /**
     * Role labels
     * @return array
     */
    public static function roles() {
        return [
            self::ROLE_CUSTOMER => 'customer',
            self::ROLE_MANAGER => 'manager',
            self::ROLE_DESIGNER => 'designer',
            self::ROLE_ADMIN => 'administrator',
            self::ROLE_1C => '1c admin'
        ];
    }

    /**
     * Status labels
     * @return array
     */
    public static function statuses() {
        return [
            self::STATUS_REGISTERED => 'registered',
            self::STATUS_ACTIVE => 'active',
            self::STATUS_SUSPENDED => 'suspended'
        ];
    }

    /**
     * {@inheritdoc}
     * @return UserHandler
     */
    public function getHandler() {
        if (!$this->_handler) {
            $this->_handler = new UserHandler($this);
        }
        return $this->_handler;
    }

    /**
     * {@inheritdoc}
     * @return UserDecorator
     */
    public function getDecorator() {
        if (!$this->_decorator) {
            $this->_decorator = new UserDecorator($this);
        }
        return $this->_decorator;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at'
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
        return [
            self::SCENARIO_DEFAULT => [],
            self::SCENARIO_VIEW => [],
            self::SCENARIO_REGISTER => ['email', 'password'],
            self::SCENARIO_UPDATE => ['first_name','business_type', 'business_id', 'address', 'website' ,'middle_name', 'last_name', 'company', 'phone', 'mobile_phone','contact_guid', 'owner_guid', 'provider', 'one_c_status'],
            self::SCENARIO_MANAGER => ['first_name', 'middle_name', 'last_name', 'company', 'phone', 'mobile_phone', 'email', 'website', 'address', 'business_id', 'business_type', 'provider'],
            self::SCENARIO_ADMIN => ['first_name', 'middle_name', 'last_name', 'company', 'phone', 'mobile_phone', 'email', 'role', 'website', 'address', 'business_id', 'business_type', 'provider']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return $this->decorator->fields();
    }

    /**
     * Add `active` condition to automatically log out suspended users
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }


    /**
     * find users by contact_guid from 1C
     * {@inheritdoc}
     */
    public static function findIdentityByContactGuid($guid)
    {
        return static::findOne(['contact_guid' => $guid]);
    }


    /**
     * find users by owner_guid from 1C
     * {@inheritdoc}
     */
    public static function findIdentityByOwnerGuid($guid)
    {
        return static::findOne(['owner_guid' => $guid]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token]);

    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return self|null
     */
    public static function findByEmail($email)
    {
        $condition = ['email' => $email];
        return static::findOne($condition);
    }

    public function getHistory()
    {
        return $this->hasMany(History::class, ['user_id' => 'id']);
    }


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        try {
            return Yii::$app->security->validatePassword($password, $this->password_hash);
        }
        catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert) {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            $this->generateAuthKey();
        }
        return true;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(self::class, ['id' => 'manager_id'])->from(['manager' => '{{%user}}']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConfirmationToken()
    {
        return $this->hasOne(Token::class, ['user_id' => 'id'])
            ->onCondition(['type' => Token::TYPE_CONFIRMATION]);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMobileConfirmationToken()
    {
        return $this->hasOne(Token::class, ['user_id' => 'id'])
            ->onCondition(['type' => Token::TYPE_MOBILE_CONFIRMATION]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPasswordResetToken()
    {
        return $this->hasOne(Token::class, ['user_id' => 'id'])
            ->onCondition(['type' => Token::TYPE_PASSWORD_RESET]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjects()
    {
        return $this->hasMany(Project::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClients()
    {
        return $this->hasMany(User::class, ['manager_id' => 'id']);
    }

}
