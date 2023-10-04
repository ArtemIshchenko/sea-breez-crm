<?php

namespace app\modules\user\controllers\api;

use app\components\helpers\ErrorHelper;
use app\modules\user\models\forms\EmailForm;
use app\modules\user\models\forms\LangForm;
use app\modules\user\models\forms\PasswordForm;
use app\modules\user\models\forms\UserForm;
use app\modules\user\models\User;
use linslin\yii2\curl\Curl;

use Yii;
use yii\base\Action;

class ProfileController extends \app\modules\api\components\controllers\Controller
{
    /**
     * Updates current user's model
     * @return User|UserForm User's model or form model in case of validation errors.
     */
    public function actionUpdate()
    {
        $data = Yii::$app->getRequest()->getBodyParams();
        $model = Yii::$app->user->identity;
        $init = !$model->last_name;
        $form = new UserForm([
            'record' => $model,
            'scenario' => $init ? UserForm::SCENARIO_INIT : UserForm::SCENARIO_PROFILE
        ]);

        if (!$form->load($data, '')) {
            ErrorHelper::throwInputNotLoaded();
        }
        if (!$form->save()) {
            ErrorHelper::checkModelHasErrors($form);
            return $form;
        }
        return $form->record;
    }

    /**
     * Updates current user's password
     * @return User|PasswordForm User's model or form model in case of validation errors.
     */
    public function actionUpdatePassword()
    {
        $form = new PasswordForm([
            'record' => Yii::$app->user->identity
        ]);
        if (!$form->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            ErrorHelper::throwInputNotLoaded();
        }
        if (!$form->save()) {
            ErrorHelper::checkModelHasErrors($form);
            return $form;
        }
        return $form->record;
    }

    public function actionResetCode(){
        $model = Yii::$app->user->identity;
        $model->handler->resetConfirmationToken();
        return [
            'success' => 'success',
        ];
    }
    public function debugLocal($data,$json = false){
        $curl = new Curl();
        $curl->setRawPostData($data);
        if($json){
            $response = $curl->setHeaders([
                'Content-Type' => 'application/json',
            ]);
        }
        $response = $curl
            ->post('debug.mebug/api/send');
        return $response;
    }
    /**
     * @return string[]
     */
    public function actionSendCode()
    {
        $phone =  Yii::$app->getRequest()->getBodyParam('number');
        $model = Yii::$app->user->identity;
        $stringSpace = '0123456789';
        $code = '';
        for ($i = 0; $i < 4; $i ++) {
            $code .= $stringSpace[rand(0, 9)];
        }
        $model->handler->generateMobileConfirmationToken($code);
        $model->mobile_phone = $phone;
        $model->mobile_phone_verified = false;
        $model->mobileConfirmationToken->save();
        $model->save();
        $curl = new Curl();
        $data = [
            "contacts" => [
                [
                    "channels"=> [
                        [
                            "type"=> "sms",
                            "value"=> preg_replace('/[^0-9.]+/', '', $phone)
                        ],
                        [
                            "type"=> "email",
                            "value"=> $model->email
                        ]
                    ],
                    "fields"=> [
                        [
                            "id"=> 228885,
                            "value"=> $code
                        ],
                        [
                            "id"=> "231766",
                            "value"=> $model->id
                        ]
                    ]
                ]
            ],
            "contactFields"=> [
                "sms"
            ],
            "dedupeOn" => "email",
            "customFieldsIDs"=> [
                228885,
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
        $model->save();
        return [
            'success' => 'success',
            'number' => $phone,
            'config' => $model->mobileConfirmationToken->additional
        ];
    }



    /**
     * @return string[]
     */
    public function actionVerifyPhone()
    {
        $user = Yii::$app->user->identity;
        $code = Yii::$app->getRequest()->getBodyParam('code');
        $out_of_limit = false;
        $recalculate = false;
        if ($user->mobileConfirmationToken) {
            $additional = $user->mobileConfirmationToken->additional;
            if($additional['attempts'] >= 3){
                $out_of_limit = true;
                $diff = time() - $additional['sms_sent'];
                if($diff >= 10){
                    $recalculate = true;
                    $out_of_limit = false;
                }
            }
            if($out_of_limit){
                return [
                    'success' => 'error',
                    'message' => 'Вы превысили лимит попыток за минуту (3/3)',
                    'diff' => time() - $additional['sms_sent']
                ];
            }elseif (Yii::$app->getSecurity()->validatePassword($code, $user->mobileConfirmationToken->value )){
                $user->unlink('mobileConfirmationToken',$user->mobileConfirmationToken,true);
                $user->mobile_phone_verified = true;
                $user->save();
                return [
                    'success' => 'success',
                    'user' => $user,
                ];
            }else{
                $user->handler->wrongCodeAttempt($recalculate);
                $additional = $user->mobileConfirmationToken->additional;
                if($additional['attempts'] >= 3){
                    $message = 'Вы превысили лимит попыток за минуту (3/3)';
                }else{
                    $message = 'Неправильный код';
                }
                return [
                    'success' => 'error',
                    'message' => $message,
                    'config' => $user->mobileConfirmationToken->additional,
                ];
            }


        } else {
            return [
                'success' => 'error',
                'message' => 'Неправильный код'
            ];
        }
    }



    /**
     * Updates current user's email
     * @return User|EmailForm User's model or form model in case of validation errors.
     */
    public function actionUpdateEmail()
    {
        $form = new EmailForm([
            'record' => Yii::$app->user->identity
        ]);
        if (!$form->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            ErrorHelper::throwInputNotLoaded();
        }
        if (!$form->save()) {
            ErrorHelper::checkModelHasErrors($form);
            return $form;
        }
        Yii::$app->session->setFlash('success', Yii::t('user', 'Your email was successfully changed . Confirmation letter was sent to your email.'));

        return $form->record;
    }

    /**
     * Updates current user's email
     * @return User|EmailForm User's model or form model in case of validation errors.
     */
    public function actionUpdateLang()
    {
        $form = new LangForm([
            'record' => Yii::$app->user->identity
        ]);
        if (!$form->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            ErrorHelper::throwInputNotLoaded();
        }
        if (!$form->save()) {
            ErrorHelper::checkModelHasErrors($form);
            return $form;
        }
        Yii::$app->session->setFlash('success', Yii::t('user', 'Your language was successfully changed.'));

        return $form->record;
    }

    public function sendBusinessInfo($model,$data,$url){
        $curl = new Curl();
        $response = $curl->setOption(
            CURLOPT_POSTFIELDS,
            http_build_query(array(
                    "companyName"            => $data["company"],
                    "companyEdrpou"          => $data["business_type"] === 'company' ? $data["business_id"] : null,
                    "companyIpn"             => $data["business_type"] === 'private' ? $data["business_id"] : null,
                    "partnerSite"            => $data["website"],
                    "partnerName"            => $data["first_name"],
                    "partnerMidName"         => $data["middle_name"],
                    "partnerLastName"        => $data["last_name"],
                    "partnerEmail"           => $model->email,
                    "partnerCellPhoneNumber" => $data["mobile_phone"],
                    "partnerWorkPhoneNumber" => $data["phone"],
                    "partnerAddress"         => $data["address"],
                    "partnerInfo"            => '',
                    "partnerIdPS"            => $model->id
                )
            ))
            ->post($url);
    }

}
