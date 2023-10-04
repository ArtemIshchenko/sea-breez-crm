<?php

namespace app\modules\user\models\forms;

use linslin\yii2\curl\Curl;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use app\components\interfaces\Form;
use app\modules\user\models\{handlers\UserHandler, User, Comment};
use app\modules\user\helpers\UserHelper;

/**
 * User update form
 *
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property string $company
 * @property string $phone
 * @property string $mobile_phone
 * @property string $business_type
 * @property string $website
 * @property string $address
 * @property string $business_id
 * @property string $manager_data
 * @property string $email
 * @property string $role
 * @property string $provider
 */
class UserForm extends Model
{
    const SCENARIO_INIT = 'init';
    const SCENARIO_PROFILE = 'profile';
    const SCENARIO_ADMIN = 'admin';
    const SCENARIO_MANAGER = 'manager';


    public $first_name;
    public $middle_name;
    public $last_name;
    public $company;
    public $business_type;
    public $business_id;
    public $website;
    public $address;
    public $provider;
    public $phone;
    public $mobile_phone;

    //Init only
    public $manager_data;

    // Admin only
    public $email;

    // Admin only
    public $role;

    /**
     * If user submits this form for the first time
     * @var bool
     */
    private $_isInitForm;

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
     * @param User $record
     */
    public function setRecord($record) {
        $this->_record = $record;
    }

    /**
     * {@inheritdoc}
     */
    public function init() {
        parent::init();
        $this->setAttributes($this->record->attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios() {
        return [
            self::SCENARIO_INIT => ['first_name', 'middle_name', 'last_name', 'company', 'phone', 'mobile_phone', 'manager_data','business_type', 'provider', 'address', 'business_id', 'website'],
            self::SCENARIO_PROFILE => ['first_name', 'middle_name', 'last_name', 'company', 'phone', 'mobile_phone', 'business_type', 'business_id', 'address', 'website', 'provider'],
            self::SCENARIO_MANAGER => ['first_name', 'middle_name', 'last_name', 'company', 'phone', 'mobile_phone', 'email', 'website', 'address', 'business_id', 'business_type', 'provider'],
            self::SCENARIO_ADMIN => ['first_name', 'middle_name', 'last_name', 'company', 'phone', 'mobile_phone', 'email', 'role', 'website', 'business_id','business_type', 'address', 'provider']
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['first_name', 'middle_name', 'last_name', 'company', 'phone', 'mobile_phone','website' ,'email', 'manager_data'], 'string', 'max' => 255],
            [['first_name', 'middle_name', 'last_name', 'company', 'phone', 'mobile_phone', 'email'], 'trim'],
            [['last_name', 'company', 'mobile_phone', 'email', 'role'], 'required'],
            // [['phone', 'mobile_phone'], 'match', 'pattern' => '/^[0-9\- \(\)]+$/i', 'message' => Yii::t('user', '{attribute} may contain only numbers, brackets, space and hyphen.')],
            [['first_name', 'middle_name', 'last_name', 'company', 'phone', 'mobile_phone'], 'filter', 'filter' => [HtmlPurifier::class, 'process']],
            [['email'], 'email'],
            [['email'], 'unique', 'targetClass' => User::class, 'message' => Yii::t('user', 'User with this email is already registered.'), 'when' => function($model, $attribute) {
                return $this->record->email != $model->email;
            }],
            [['role'], 'validateRole']
        ];
    }

    /**
     * Vaidates role and sets role id instead of role label to `role` property
     * @param string $attribute the attribute currently being validated
     * @param mixed $params the value of the "params" given in the rule
     * @param \yii\validators\InlineValidator $validator related InlineValidator instance.
     */
    public function validateRole($attribute, $params, $validator) {
        $role = array_search($this->role, User::roles());
        if (!$role) {
            $this->addError('role', Yii::t('user', 'Unknown user role.'));
        }
        else {
            $this->role = $role;
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'first_name' => Yii::t('user', 'First name'),
            'middle_name' => Yii::t('user', 'Middle name'),
            'last_name' => Yii::t('user', 'Last name'),
            'company' => Yii::t('user', 'Company'),
            'phone' => Yii::t('user', 'Phone'),
            'mobile_phone' => Yii::t('user', 'Mobile phone'),
            'email' => Yii::t('user', 'Email'),
            'manager_data' => Yii::t('user', 'Manager data'),
        ];
    }


    public function saveContactFormChanges($model,$data){
        $all_changes = [];
        $action = 'Изменение контактных данных';
        $only_action = '';
        $type = '';
        $changedAttributes = [];
        $action_titles = [
            'phone' => [
                'adding' => 'Добавление рабочего номера',
                'deleting' => 'Удаление рабочего номера',
                'editing' => 'Изменение рабочего номера'
            ],
            'website' => [
                'adding' => 'Добавление сайта',
                'deleting' => 'Удаление сайта',
                'editing' => 'Изменение сайта'
            ],
            'address' => [
                'adding' => 'Добавление адреса',
                'deleting' => 'Удаление адреса',
                'editing' => 'Изменение адреса'
            ],
            'mobile_phone' => [
                'adding' => 'Добавление мобильного номера',
                'deleting' => 'Удаление мобильного номера',
                'editing' => 'Изменение мобильного номера'
            ],
            'email' => [
                'adding' => 'Добавление почты',
                'deleting' => 'Удаление почты',
                'editing' => 'Изменение почты'
            ],
            'business_id' => [
                'adding' => 'Добавление ЕГРПОУ/ИНН',
                'deleting' => 'Удаление ЕГРПОУ/ИНН',
                'editing' => 'Изменение ЕГРПОУ/ИНН'
            ]
        ];
        $action_fields = [
            'phone' => ' раб. телефон',
            'website' => ' сайт',
            'address' => ' адрес',
            'mobile_phone' => ' моб. телефон',
            'email' => ' email',
            'business_id' => ' ЕГРПОУ/ИНН',

        ];
        $has_changes = false;
        foreach ($action_fields as $key => $value){
            if($data[$key] !== $model[$key]){
                array_push($all_changes,$key);
                if($data[$key] && $model[$key]){
                    $changedAttributes[$key] = $value.': '.$model[$key].' - '.$data[$key];
                    $only_action = $action_titles[$key]['editing'];
                    $type = 'editing';
                }else{
                    if($model[$key]){
                        $changedAttributes[$key] = $value.': '.$model[$key];
                        $only_action = $action_titles[$key]['deleting'];
                        $type = 'deleting';
                    }else{
                        $changedAttributes[$key] = $value.': '.$data[$key];
                        $only_action = $action_titles[$key]['adding'];
                        $type = 'adding';
                    }
                }
                $has_changes = true;
            }
        }
        if($has_changes && count($changedAttributes) === 1){
            $action = $only_action;
        }else{
            $type = 'editing';
        }
        if($has_changes){
            $this->record->handler->addToHistory($action, "Измененные данные. \r\n" . implode(" \r\n", $changedAttributes),$type);
        }
        return $all_changes;
    }

    public function sendBusinessInfoChanges($url,$id){
        $curl = new Curl();
        $response = $curl->setOption(
            CURLOPT_POSTFIELDS,
            http_build_query(array(
                    "companyName"            => $this -> company,
                    "companyEdrpou"          => $this -> business_type  === 'company' ? $this -> business_id : null,
                    "companyIpn"             => $this -> business_type  === 'private' ? $this -> business_id : null,
                    "partnerSite"            => $this -> website,
                    "partnerName"            => $this -> first_name,
                    "partnerMidName"         => $this -> middle_name,
                    "partnerLastName"        => $this -> last_name,
                    "partnerEmail"           => $this -> email,
                    "partnerCellPhoneNumber" => $this -> mobile_phone,
                    "partnerWorkPhoneNumber" => $this -> phone,
                    "partnerAddress"         => $this -> address,
                    "partnerInfo"            => '',
                    "partnerIdPS"            => $id
                )
            ))
            ->post($url);
    }
    public function currentContactValues($record){
        return [
            'phone'        => $record -> phone,
            'mobile_phone' => $record -> mobile_phone,
            'email'        => $record -> email,
            'website'      => $record -> website,
            'address'      => $record -> address,
            'business_id'  => $record -> business_id
        ];
    }
    public function save() {
        $user = $this->record;
        if (!$this->validate() || !$user) {
            return false;
        }
        $user->scenario = $this->_getRecordScenario();
        $model_to_check = $this->currentContactValues($this->record);
        if($this->record ->mobile_phone !== $this->mobile_phone){
            $user->mobile_phone_verified = false;
            if($user->mobileConfirmationToken){
                $user->unlink('mobileConfirmationToken', $user->mobileConfirmationToken, true);
            }
        }
        $should_send_business_update = $this->business_id && $this->record->business_id !== $this->business_id;
        $user->setAttributes($this->attributes);
        if ($user->save()) {
            $this->saveContactFormChanges($model_to_check,ArrayHelper::toArray($this->attributes));
            if($should_send_business_update){
                $this->sendBusinessInfoChanges(Yii::$app->params['VIASPUTNIK']['url'],$user->id);
            }
            if ($this->scenario == self::SCENARIO_INIT) {
                if ($this->manager_data) {
                    $comment = new Comment([
                        'body' => Yii::t('user', 'Manager data') . "\n" . $this->manager_data
                    ]);
                    try {
                        $comment->link('user', $user);
                    }
                    catch (\Exception $e) {
                        Yii::error($e->getMessage());
                        var_dump($e->getMessage());exit;
                    }
                }
//                Yii::$app->mailer
//                    ->compose([
//                        'html' => 'customerConfirmed-html',
//                        'text' => 'customerConfirmed-text'
//                    ], [
//                        'url' => Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['adminAppUrl'], '#' => '/users/' . $user->id]),
//                        'user' => $user->toArray(),
//                        'managerData' => $this->manager_data
//                    ])
//                    ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
//                    ->setTo(UserHelper::administratorEmails())
//                    ->setSubject(Yii::t('user', 'New user confirmed registration.'))
//                    ->send();
                $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['adminAppUrl'], '#' => '/users/' . $user->id]);

                foreach (UserHelper::administratorEmails() as $email) {
                    $user->handler->generateEvent(
                        $email,
                        UserHandler::EVENT_TYPE_KEY['newUserConfirmedRegistration'],
                        $url,
                        [
                            'managerData' => Html::encode($this->manager_data ?: 'Нема даних'),
                            'clientEmail' => Html::encode($user->email),
                            'lastName' => Html::encode($user->last_name),
                            'firstName' => Html::encode($user->first_name),
                            'middleName' => Html::encode($user->middle_name),
                            'company' => Html::encode($user->company),
                            'phone' => Html::encode($user->mobile_phone),
                            'subject' => Yii::t('user', 'New user confirmed registration.'),
                        ]
                    );
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Returns User record scenario according to set form scenario
     * @return string
     */
    private function _getRecordScenario()
    {
        switch ($this->scenario) {
            case self::SCENARIO_ADMIN:
                return User::SCENARIO_ADMIN;
            case self::SCENARIO_MANAGER:
                return User::SCENARIO_MANAGER;
            default:
                return User::SCENARIO_UPDATE;
        }
    }
}