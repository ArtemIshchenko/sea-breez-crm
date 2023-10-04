<?php

namespace app\modules\user\models\handlers;

use app\modules\user\models\History;
use linslin\yii2\curl\Curl;
use Yii;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\web\ServerErrorHttpException;
use app\components\Handler;
use app\modules\user\helpers\UserHelper;
use app\modules\user\models\{forms\GuidForm, User, Token};
use function Sodium\add;

class UserHandler extends Handler
{

    const EVENT_TYPE_KEY = [
        'newUserConfirmedRegistration' => 'newUserConfirmedRegistration',
        'passwordResetEvent' => 'passwordResetEvent',
        'emailAddressUpdated' => 'emailAddressUpdated',
        'confirmationUpdate' => 'confirmationUpdate',
        'newCustomerWasAppointed' => 'newCustomerWasAppointed',
        'managerAssigned' => 'managerAssigned',
        'projectSentWithoutGuid' => 'projectSentWithoutGuid',
        'newUserPasswordSent' => 'newUserPasswordSent',
        'notificationProjectDeadline' => 'notificationProjectDeadline',
        'notificationProjectDelay' => 'notificationProjectDelay',
        'notificationProjectCanceled' => 'notificationProjectCanceled',
        'notificationSpecificationReturned' => 'notificationSpecificationReturned',
        'notificationSpecificationSet' => 'notificationSpecificationSet',
        'notificationProjectReturned' => 'notificationProjectReturned',
        'notificationProjectRejected' => 'notificationProjectRejected',
        'notificationDesignerAssigned' => 'notificationDesignerAssigned',
        'notificationManagementSentProjectWithSomeData' => 'notificationManagementSentProjectWithSomeData',
        'notificationManagementProjectCanceled' => 'notificationManagementProjectCanceled',
        'notificationManagementSpecificationReturned' => 'notificationManagementSpecificationReturned',
        'notificationManagementProjectCreated' => 'notificationManagementProjectCreated',
        'notificationManagementSpecificationAccepted' => 'notificationManagementSpecificationAccepted',
    ];

    public function sendToEsputnik($model,$link){
        $curl = new Curl();
        $data = [
            "contacts" => [
                [
                    "channels" => [
                        [
                            "type" => "email",
                            "value" => $model->email
                        ],
                    ],
                    "fields" => [
                        [
                            "id" => "231766",
                            "value" => $model->id
                        ],
                        [
                            "id" => 228783,
                            "value" => $link
                        ],

                    ],
                ],
            ],
            "contactFields" => [
                "email"
            ],
            "dedupeOn" => "email",
            "customFieldsIDs" => [
                228783,
                "231766"
            ]
        ];
        $response = $curl
            ->setRawPostData(json_encode($data))
            ->setHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => Yii::$app->params['ESPUTNIK_AUTH_KEY']
            ])
            ->post(Yii::$app->params['ESPUTNIK_STORE_URL']);
        return $response;
    }

    public function sendToEsputnikItv($model){
        $curl = new Curl();
        $data = [
            "contacts" => [
                [
                    "channels" => [
                        [
                            "type" => "email",
                            "value" => $model->email
                        ],
                    ],
                    "fields" => [
                        [
                            "id" => 237075,
                            "value" => $model->id
                        ],
                    ],
                ],
            ],
            "contactFields" => [
                "email"
            ],
            "dedupeOn" => "email",
            "customFieldsIDs" => [
                237075
            ]
        ];
        $response = $curl
            ->setRawPostData(json_encode($data))
            ->setHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => Yii::$app->params['ESPUTNIK_AUTH_KEY_ITV']
            ])
            ->post(Yii::$app->params['ESPUTNIK_STORE_URL']);
        return $response;
    }

    public function generateEvent($email, $eventTypeKey, $link = '', $extraData = [], $provider = '') {
        $curl = new Curl();
        $data = [
            'eventTypeKey' => $eventTypeKey,
            'keyValue' => $email,
            'params' => [
                [
                    'name' => 'email',
                    'value' => $email
                ]
            ]
        ];
        if (!empty($link)) {
            $data['params'][] = [
                'name' => 'link',
                'value' => $link
            ];
        }
        if (!empty($extraData)) {
            foreach ($extraData as $key => $val) {
                $data['params'][] = [
                    'name' => $key,
                    'value' => $val
                ];
            }
        }
        $apiUrl = Yii::$app->params['ESPUTNIK_GENERATE_EVENT'];
        $apiAuthKey = Yii::$app->params['ESPUTNIK_AUTH_KEY'];
        if ($provider == 'itv') {
            $apiAuthKey = Yii::$app->params['ESPUTNIK_AUTH_KEY_ITV'];
        }
        $response = $curl
            ->setRawPostData(json_encode($data))
            ->setHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => $apiAuthKey
            ])
            ->post($apiUrl);

        $curl->reset();

        return $response;
    }

    /**
     * Creates new user. Validation should be performed beforehand.
     * @throws ServerErrorHttpException in case of mailing error
     * @return bool
     */
    public function create() {
        if ($this->record->save()) {
            Yii::$app->db->transaction(function ($db){
                $this->record->handler->generateToken(Token::TYPE_CONFIRMATION);
                $link = Yii::$app->urlManager->createAbsoluteUrl([
                    'auth/confirm-registration',
                    'email' => $this->record->email,
                    'token' => $this->record->confirmationToken->value]);
                $withoutHttps =strpos($link,'https://') !== false ? substr($link,8) : substr($link,7);
                $this->sendToEsputnik($this->record,$withoutHttps);

                $sent = true;
                /*Yii::$app->mailer
                    ->compose([
                        'html' => 'confirmation-html',
                        'text' => 'confirmation-text'
                    ], [
                        'url' => Yii::$app->urlManager->createAbsoluteUrl([
                            'auth/confirm-registration',
                            'email' => $this->record->email,
                            'token' => $this->record->confirmationToken->value])
                    ])
                    ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
                    ->setTo($this->record->email)
                    ->setSubject(Yii::t('user', 'Registration completed. Email confirmation needed.'))
                    ->send();*/
                if (!$sent) {
                    throw new ServerErrorHttpException(Yii::t('user', 'Confirmation email sending failed.'));
                }
            });
            return true;
        }
        return false;
    }

    /**
     * Upgrades user status to `Active`
     * @return bool
     */
    public function confirmRegistration() {
        $this->record->status = User::STATUS_ACTIVE;
        if ($this->record->save(false)) {
            $this->login();
            $this->record->unlink('confirmationToken', $this->record->confirmationToken, true);
            return true;
        }
        return false;
    }

    /**
     * Validates given token
     * @param  string $token Given token
     * @param  int $type Token type
     * @return bool
     */
    public function validateToken($token, $type) {
        $relation = UserHelper::getTokenRelationByType($type);
        if (!$relation)
            return false;
        $savedToken = $this->record->{$relation};
        return $savedToken
            // && !$savedToken->isExpired
            && $savedToken->value === $token;
    }

    /**
     * Generates token of given type. Also overwrites old token if exists.
     * @param int $type
     * @return bool
     */
    public function generateToken($type) {
        $relation = UserHelper::getTokenRelationByType($type);
        if (!$relation)
            return false;
        if ($oldRelation = $this->record->{$relation})
            $this->record->unlink($relation, $oldRelation, true);
        $token = Yii::createObject([
            'class' => Token::class,
            'type' => $type
        ]);
        $this->record->link($relation, $token);
        return true;
    }

    public function resetConfirmationToken(){
        $relation = 'mobileConfirmationToken';
        if ($oldRelation = $this->record->{$relation})
            $this->record->unlink($relation, $oldRelation, true);
    }


    /**
     * Generates hash of given type with giver password. Also overwrites old token if exists.
     * @param int $type
     * @return bool
     */
    public function generateMobileConfirmationToken($code) {
        $relation = 'mobileConfirmationToken';
        if ($oldRelation = $this->record->{$relation})
            $this->record->unlink($relation, $oldRelation, true);
        $token = Yii::createObject([
            'class' => Token::class,
            'type' => Token::TYPE_MOBILE_CONFIRMATION,
            'value' => $code
        ]);
        $this->record->link($relation, $token);
        return true;
    }

    public function wrongCodeAttempt($recalculate = false){
        $relation = $this->record->mobileConfirmationToken;
        $additional = $relation -> additional;
        $additional['attempts'] = $recalculate ? 1 : $additional['attempts'] +1;
        $additional['sms_sent'] = $recalculate ? time() : $additional['sms_sent'];
        $relation -> additional = $additional;
        $relation->save();
        return $relation->additional['attempts'];
    }

    /**
     * Removes token of given type
     * @param  int $type
     * @return void
     */
    public function removeToken($type) {
        $relation = UserHelper::getTokenRelationByType($type);
        if ($relation && $this->record->{$relation})
            $this->record->unlink($relation, $this->record->{$relation}, true);
    }

    /**
     * Updates user's email
     * @param string $email
     * @return bool
     */
    public function updateEmail($email)
    {
        $oldEmail = $this->record->email;
        $this->record->email = $email;
        $this->record->status = User::STATUS_REGISTERED;
        if ($this->record->save()) {
            Yii::$app->user->logout();
//            Yii::$app->mailer
//                ->compose([
//                    'html' => 'emailUpdated-html',
//                    'text' => 'emailUpdated-text',
//                ], [
//                    'user' => $this->record->toArray()
//                ])
//                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
//                ->setTo($oldEmail)
//                ->setSubject(Yii::t('user', 'Email address was updated.'))
//                ->send();
            $this->generateEvent(
                $oldEmail,
                self::EVENT_TYPE_KEY['emailAddressUpdated'],
                '',
                [
                    'subject' => Yii::t('user', 'Email address was updated.'),
                    'newEmail' => Html::encode($this->record->email)
                ],
                $this->record->provider == 'itv' ? 'itv' : ''
            );

            $this->record->handler->generateToken(Token::TYPE_CONFIRMATION);
//            $sent = Yii::$app->mailer
//                ->compose([
//                    'html' => 'confirmation-update-html',
//                    'text' => 'confirmation-text'
//                ], [
//                    'url' => Yii::$app->urlManager->createAbsoluteUrl([
//                        'auth/confirm-registration',
//                        'email' => $this->record->email,
//                        'token' => $this->record->confirmationToken->value])
//                ])
//                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
//                ->setTo($this->record->email)
//                ->setSubject(Yii::t('user', 'Email changed. Confirmation needed.'))
//                ->send();
            $url = Yii::$app->urlManager->createAbsoluteUrl([
                        'auth/confirm-registration',
                        'email' => $this->record->email,
                        'token' => $this->record->confirmationToken->value]);
            $sent = $this->generateEvent(
                $this->record->email,
                self::EVENT_TYPE_KEY['confirmationUpdate'],
                $url,
                [
                    'subject' => Yii::t('user', 'Email changed. Confirmation needed'),
                ],
                $this->record->provider == 'itv' ? 'itv' : ''
            );

            return true;
        }
        return false;
    }
    /**
     * Adds action to project history.
     * @param string $action
     * @param string|null $additional
     * @return void
     */
    public function addToHistory($action, $additional = null,$type = null) {
        $story = new History([
            'action' => $action,
            'additional' => HtmlPurifier::process($additional),
            'type' => $type
        ]);
        $this->record->link('history', $story);
    }
    public function verify1CStatus($response){
        $data = json_decode($response);
        if($data && $data->status){
            switch ($data->status){
                case "EXIST":
                    if($data->contact_guid && $data->partner_guid){
                        $form = new GuidForm([
                            'record' => $this->record,
                        ]);
                        if (!$form->load([
                                'contact_guid' => $data->contact_guid,
                                'owner_guid' => $data->partner_guid,
                                'one_c_status' => User::ONE_C_STATUS_EXIST
                            ], '') || !$form->save()) {
                            $this->record->one_c_status = User::ONE_C_STATUS_ERROR;
                            $this->record->save();
                        }
                    }else{
                        $this->record->one_c_status = User::ONE_C_STATUS_ERROR;
                        $this->record->save();
                    }
                    break;
                case "MODERATION":
                    $this->record->one_c_status = User::ONE_C_STATUS_MODERATION;
                    $this->record->save();
            }
        }else{
            $this->record->one_c_status = User::ONE_C_STATUS_ERROR;
            $this->record->save();
        }
    }

    public function requestTo1C($body,$url){
        $curl = new Curl();
        $data = [
            "request_key" => Yii::$app->params['ONE_C_INTEGRATION']['request_key'],
            "request_type" => "POST",
            "url" => $url,
            "body" => json_encode($body)
        ];
        $response = $curl
            ->setRawPostData(json_encode($data))
            ->setHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => Yii::$app->params['ONE_C_INTEGRATION']['Authorization'],
                'Client-Id' => Yii::$app->params['ONE_C_INTEGRATION']['client_id']
            ])
            ->post(Yii::$app->params['ONE_C_INTEGRATION']['proxy']);
        return (Object) [
            'code' => $curl->responseCode,
            'response' => $response
        ];
    }
    public function sendClientTo1C(){
        $user = $this->record;
//        $created = [
//            "partnerEmail" => "app@gmail.com",
//            "partnerIdPS" => 1969,
//            "companyName" => "Eleos",
//            "partnerName" => "Eleos",
//            "name" => "Semen Petrovich",
//            "partnerPhoneNumber" => "380683619238",
//            "partnerAddress" => "Petrova 23",
//            "partnerSite" => "FEX.UA",
//            "partnerInfo" => "info",
//            "companyEdrpou" => "023454323",
//            "companyIpn" => "1234567890"
//        ];
//        $moderation = [
//            "partnerEmail" => "1info@deviceplus.com.ua",
//            "partnerIdPS" => 1234567823,
//            "companyName" => "Eleos",
//            "partnerName" => "Eleos",
//            "name" => "Semen Petrovich",
//            "partnerPhoneNumber" => "1380904439762",
//            "partnerAddress" => "Petrova 23",
//            "partnerSite" => "FEX.UA",
//            "partnerInfo" => "info",
//            "companyEdrpou" => "023454351",
//            "companyIpn" => "123455651"
//        ];
        if(!$user->contact_guid){
            $body = [
                "partnerEmail" => $user->email,
                "partnerIdPS" => $user->id,
                "companyName" => $user->company,
                "partnerName" => $user->company,
                "name" => $user->first_name.' '.$user->last_name,
                "partnerPhoneNumber" => preg_replace('/[^0-9.]+/', '', $user->mobile_phone),
                "partnerAddress" => $user->address,
                "partnerSite" => $user->website,
                "partnerInfo" => null,
                "companyEdrpou" => $user -> business_type  === 'company' ? $user -> business_id : null,
                "companyIpn" => $user -> business_type  === 'private' ? $user -> business_id : null,
            ];
            //$this->debugLocal($body);
            $request = $this->requestTo1C($body,Yii::$app->params['ONE_C_INTEGRATION']['register_client_url']);
            switch ($request->code){
                case 200:
                    //$this->debugLocal('200');
                    //$this->debugLocal($request->response,true);
                    $this->verify1CStatus($request->response);
                    break;
                default:
                    //$this->debugLocal('error');
                    return false;
            }
        }


    }

    /**
     * Updates customer's manager
     * @param int $managerId
     * @return bool
     */
    public function updateManager($managerId)
    {
        $this->record->manager_id = $managerId;
        $manager = User::findIdentity($managerId);
        if ($manager) {
            if ($manager->provider) {
                $this->record->provider = $manager->provider;
            }
            if ($this->record->save()) {

//            Yii::$app->mailer
//                ->compose([
//                    'html' => 'managerSet-html',
//                    'text' => 'managerSet-text'
//                ], [
//                    'user' => $this->record->toArray(),
//                    'url' => Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['managerAppUrl'], '#' => '/users/' . $this->record->id])
//                ])
//                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
//                ->setTo($this->record->manager->email)
//                ->setSubject(Yii::t('user', 'New customer was appointed to you.'))
//                ->send();
                // message for manager
                $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['managerAppUrl'], '#' => '/users/' . $this->record->id]);
                $this->generateEvent(
                    $this->record->manager->email,
                    UserHandler::EVENT_TYPE_KEY['newCustomerWasAppointed'],
                    $url,
                    [
                        'firstName' => $this->record->first_name,
                        'lastName' => $this->record->last_name,
                        'subject' => Yii::t('user', 'New customer was appointed to you.')
                    ]
                );

                // message for user
                if ($this->record->provider === 'itv') {
                    $this->sendToEsputnikItv($this->record);
                } else {
                    $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['adminAppUrl'], '#' => '/users/' . $this->record->id]);
                    $this->generateEvent(
                        $this->record->email,
                        UserHandler::EVENT_TYPE_KEY['managerAssigned'],
                        $url,
                        [
                            'firstName' => $this->record->first_name,
                            'lastName' => $this->record->last_name,
                            'subject' => Yii::t('user', 'Manager assigned to your project.'),
                            'provider' => $this->record->provider,
                        ]
                    );
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Updates user status.
     * @param  int|string $newStatus New status id or label
     * @return bool If status updated.
     */
    public function updateStatus($newStatus)
    {
        if (!array_key_exists($newStatus, User::statuses())) {
            $newStatus = array_search($newStatus, User::statuses());
            if (!$newStatus)
                return false;
        }
        $this->record->status = $newStatus;
        return $this->record->save(false);
    }

    /**
     * Login using this user model.
     * @param  integer $duration Number of seconds that the user can remain in logged-in status, defaults to 0
     * @return bool
     */
    public function login(int $duration = 0) {
        $result = Yii::$app->user->login($this->record, $duration);
        if ($result) {
            $this->record->entered_at = time();
            $this->record->save(false, ['entered_at']);
        }
        return $result;
    }

    /**
     * Send email notification to user.
     * @param string $notification
     * @param string $subject
     * @param array $params
     * @return bool
     */
    public function notify($notification, $subject, $params = []) {

//        return Yii::$app
//           ->mailer
//           ->compose([
//               'html' => "$notification-html",
//               'text' => "$notification-text"
//           ], $params)
//           ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
//           ->setTo($this->record->email)
//           ->setSubject($subject)
//           ->send();
        $eventName = 'notification' . ucfirst($notification);
        $url = '';
        $data = [];
        switch ($eventName) {
            case self::EVENT_TYPE_KEY['notificationProjectDeadline']:
                $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['designerAppUrl'], '#' => '/projects/' . $params['project']->id . '/']);
                $data = [
                    'projectTitle' =>  Html::encode($params['project']->title),
                ];
                break;
            case self::EVENT_TYPE_KEY['notificationProjectDelay']:
                $url =  Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['customerAppUrl'], '#' => '/projects/' . $params['project']->id . '/']);
                $authorName = implode(' ', [$params['project']->author->first_name, $params['project']->author->middle_name]);
                $managerName = $params['project']->author->manager ? implode(' ', [$params['project']->author->manager->first_name, $params['project']->author->manager->middle_name, $params['project']->author->manager->last_name]) : null;
                $data = [
                    'authorName' =>  Html::encode($authorName),
                    'managerName' =>  Html::encode($managerName),
                    'projectLink' =>  Html::a(Html::encode($params['project']->title), $url),
                    'isManager' => (bool) $params['project']->author->manager,
                    'managerEmail' => $params['project']->author->manager->email,
                ];
                break;
            case self::EVENT_TYPE_KEY['notificationProjectCanceled']:
                $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params[$params['toRole'] . 'AppUrl'], '#' => '/projects/' . $params['project']->id . '/']);
                $data = [
                    'firstName' => Html::encode($params['project']->author->first_name),
                    'lastName' => Html::encode($params['project']->author->last_name),
                    'statusMessage' => Html::encode($params['project']->status_message)
                ];
                break;
            case self::EVENT_TYPE_KEY['notificationSpecificationReturned']:
                $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params[$params['toRole'] . 'AppUrl'], '#' => '/projects/' . $params['project']->id . '/']);
                $data = [
                    'firstName' => Html::encode($params['project']->author->first_name),
                    'lastName' => Html::encode($params['project']->author->last_name),
                    'projectTitle' => Html::encode($params['project']->title),
                ];
                break;
            case self::EVENT_TYPE_KEY['notificationProjectReturned']:
                $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['customerAppUrl'], '#' => '/projects/' . $params['project']->id]);
                $data = [
                    'projectTitle' => Html::encode($params['project']->title),
                    'statusMessage' => Html::encode($params['project']->status_message)
                ];
                break;
            case self::EVENT_TYPE_KEY['notificationProjectRejected']:
                $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['customerAppUrl'], '#' => '/projects/' . $params['project']->id]);
                $data = [
                    'projectTitle' => Html::encode($params['project']->title),
                    'statusMessage' => Html::encode($params['project']->status_message),
                    'rejectedBy' => Html::encode($params['rejectedBy']),
                ];
                break;
            case self::EVENT_TYPE_KEY['notificationSpecificationSet']:
                $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['customerAppUrl'], '#' => '/projects/' . $params['project']->id]);
                $data = [
                    'projectTitle' => Html::encode($params['project']->title)
                ];
                break;
            case self::EVENT_TYPE_KEY['notificationDesignerAssigned']:
                $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['designerAppUrl'], '#' => '/projects/' . $params['project']->id . '/']);
                $data = [
                    'projectTitle' => Html::encode($params['project']->title),
                    'deadlineDate' => date('d.m.Y', $params['project']->designing_deadline)
                ];
                break;

        }

        $this->generateEvent(
            $this->record->email,
            self::EVENT_TYPE_KEY[$eventName],
            $url,
            array_merge(
                ['subject' => $subject],
                $data
            ),
            isset($params['project']) && ($params['project']->author->first_name == 'itv') ? 'itv' : ''
        );

        return true;
    }

    /**
     * Send email notification to management personnel of user.
     * @param string $notification
     * @param string $subject
     * @param array $params
     * @return bool
     */
    public function notifyManagement($notification, $subject, $params = []) {
        $management = $this->record->manager ? $this->record->manager->email : UserHelper::administratorEmails();
//        return Yii::$app
//           ->mailer
//           ->compose([
//               'html' => "$notification-html",
//               'text' => "$notification-text"
//           ], array_merge(['toRole' => $this->record->manager ? 'manager' : 'admin'], $params))
//           ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
//           ->setTo($management)
//           ->setSubject($subject)
//           ->send();
        $toRole = $this->record->manager ? 'manager' : 'admin';
        $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params[$toRole . 'AppUrl'], '#' => '/projects/' . $params['project']->id . '/']);
        $eventName = 'notificationManagement' . ucfirst($notification);
        $data = [];
        switch ($eventName) {
            case self::EVENT_TYPE_KEY['notificationManagementProjectCanceled']:
                    $data = [
                        'firstName' => Html::encode($params['project']->author->first_name),
                        'lastName' => Html::encode($params['project']->author->last_name),
                        'statusMessage' => Html::encode($params['project']->status_message),
                    ];
                    break;
                case self::EVENT_TYPE_KEY['notificationManagementSpecificationReturned']:
                    $data = [
                        'firstName' => Html::encode($params['project']->author->first_name),
                        'lastName' => Html::encode($params['project']->author->last_name),
                        'projectTitle' => Html::encode($params['project']->title),
                    ];
                    break;
                case self::EVENT_TYPE_KEY['notificationManagementProjectCreated']:
                    $files = [];
                    foreach ($params['project']->files as $file) {
                        $files[] = $file->filename;
                    }
                    $data = [
                        'firstName' => Html::encode($params['project']->author->first_name),
                        'lastName' => Html::encode($params['project']->author->last_name),
                        'middleName' => Html::encode($params['project']->author->middle_name),
                        'company' => Html::encode($params['project']->author->company),
                        'projectTitle' => Html::encode($params['project']->title),
                        'projectDate' => Yii::$app->formatter->asDate($params['project']->date, 'long'),
                        'customer' => Html::encode($params['project']->client ?: 'Нет данных'),
                        'subcontractor' => Html::encode($params['project']->subcontractor ?: 'Нет данных'),
                        'revisionDescription' => $params['project']->revision_description ? 'да' : 'нет',
                        'developmentProspects' => $params['project']->development_prospects ? 'да' : 'нет',
                        'files' => $files ? Html::encode(implode(', ', $files)) : 'Нет данных'
                    ];
                    break;
                case self::EVENT_TYPE_KEY['notificationManagementSpecificationAccepted']:
                    $data = [
                        'firstName' => Html::encode($params['project']->author->first_name),
                        'lastName' => Html::encode($params['project']->author->last_name),
                        'projectTitle' => Html::encode($params['project']->title),
                    ];
                    break;
                case self::EVENT_TYPE_KEY['notificationManagementSentProjectWithSomeData']:
                    $data = [
                        'projectTitle' => Html::encode($params['project']->title),
                        'projectAddress' => Html::encode($params['project']->address),
                        'projectClient' => Html::encode($params['project']->client),
                        'idsSimilarProjects' => implode(' ,', $params['idsSimilarProjects']),
                    ];
                    $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['adminAppUrl'], '#' => '/projects/' . $params['project']->id . '/']);
                    break;

        }

        if (is_array($management)) {
            foreach ($management as $email) {
                $this->generateEvent(
                    $email,
                    self::EVENT_TYPE_KEY[$eventName],
                    $url,
                    array_merge(
                        ['subject' => $subject],
                        $data
                    )
                );
            }
        } else {
            $this->generateEvent(
                $management,
                self::EVENT_TYPE_KEY[$eventName],
                $url,
                array_merge(
                    ['subject' => $subject],
                    $data
                )
            );
        }

        return true;
    }
}