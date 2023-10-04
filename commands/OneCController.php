<?php

namespace app\commands;

use app\modules\user\models\forms\GuidForm;
use app\modules\user\models\User;
use linslin\yii2\curl\Curl;
use Yii;
use yii\console\Controller;

class OneCController extends Controller
{
    public $cycle;
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
    public function checkVerification($user){
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
        $request = $this->requestTo1C($body,Yii::$app->params['ONE_C_INTEGRATION']['check_client_url']);
        return (Object) [
            'code' => $request->code,
            'response' => json_decode($request->response)
        ];
    }
    public function verify1CStatus($response,$user){
        $data = json_decode($response);
        if($data && $data->status){
            switch ($data->status){
                case "EXIST":
                    if($data->contact_guid && $data->partner_guid){
                        $form = new GuidForm([
                            'record' => $user,
                        ]);
                        if (!$form->load([
                                'contact_guid' => $data->contact_guid,
                                'owner_guid' => $data->partner_guid,
                                'one_c_status' => User::ONE_C_STATUS_EXIST
                            ], '') || !$form->save()) {
                            $user->one_c_status = User::ONE_C_STATUS_ERROR;
                            $user->save();
                        }
                    }else{
                        $user->one_c_status = User::ONE_C_STATUS_ERROR;
                        $user->save();
                    }

                    break;
                case "MODERATION":
                    $user->one_c_status = User::ONE_C_STATUS_MODERATION;
                    $user->save();
            }
        }else{
            $user->one_c_status = User::ONE_C_STATUS_ERROR;
            $user->save();
        }
    }
    public function getOneCGuid($user){
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
        $request = $this->requestTo1C($body,Yii::$app->params['ONE_C_INTEGRATION']['register_client_url']);
        switch ($request->code){
            case 200:
                $this->verify1CStatus($request->response,$user);
                break;
            default:
                return false;
        }

    }
    public function actionCheckUpdates(){
        $users = User::find()
            ->where([
                'one_c_status' => User::ONE_C_STATUS_MODERATION
            ])
            ->all();
        foreach ($users as $user) {
            $request = $this->checkVerification($user);
            if($request->code === 200){
                switch ($request->response->status){
                    case "REGISTERED AND VERIFIED":
                        $this->getOneCGuid($user);
                        break;
                }
            }
        }
    }
    /**
     * @return array
     * @throws \Exception
     */
    public function checkGuid($guid)
    {
        $link = Yii::$app->params['ONE_C_INTEGRATION']['customer_url'];
        $curl = new Curl();
        $data = [
            "request_key" => Yii::$app->params['ONE_C_INTEGRATION']['request_key'],
            "request_type" => "GET",
            "url" => $link.$guid
        ];
        $response = $curl
            ->setRawPostData(json_encode($data))
            ->setHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => Yii::$app->params['ONE_C_INTEGRATION']['Authorization'],
                'Client-Id' => Yii::$app->params['ONE_C_INTEGRATION']['client_id']
            ])
            ->post(Yii::$app->params['ONE_C_INTEGRATION']['proxy']);
        if($response){
            return (Object)[
                'status' => 'success',
                'response' => $response
            ];
        }else{
            return(Object)[
                'status' => 'error',
                'response' => $response
            ];
        }
    }
    public function actionUpdateProviders(){
    }

    public function actionCollectViatec(){
        $customers = User::find()->where(['role' => User::ROLE_CUSTOMER])->all();
        $FINAL = [];
        foreach ($customers as $customer){
            foreach ($customer->projects as $project){
                if($project->created_at > strtotime('2021-01-01')){
                    $FINAL[] = [
                        'first_name' => $customer->first_name,
                        'last_name' => $customer->last_name,
                        'middle_name' => $customer->middle_name,
                        'email' => $customer->email,
                        'company' => $customer->company,
                        'id' => $customer->id,
                        'contact_guid' => $customer->contact_guid,
                        'owner_guid' => $customer->owner_guid,
                        'manager_id' => $customer->manager->id,
                        'manager_guid' => $customer->manager->contact_guid,
                        'business_id' => $customer->business_id,
                        'phone' => $customer->phone,
                        'mobile_phone' => $customer->mobile_phone,
                        'website' => $customer->website,
                        'address' => $customer->address,
                        'mobile_phone_verified' => $customer->mobile_phone_verified ? "YES" : "NO",
                    ];
                    break;
                }

            }
        }
        file_put_contents('./matched.json', json_encode($FINAL));
    }
    public function actionUpdateManagers(){
        $customers = User::find()
            ->where(['role' => User::ROLE_CUSTOMER])
            ->andWhere(['not', ['contact_guid' => [null,'']]])
            ->all();
        foreach ($customers as $customer){
            $request = $this->checkGuid($customer->contact_guid);
            if($request->status === 'success'){
                $res = json_decode($request->response,true);
                if($res){
                    if($res['contacts'][0]
                        && array_key_exists('manager-guid',$res['contacts'][0])){
                        $manager = User::findIdentityByContactGuid($res['contacts'][0]['manager-guid']);
                        if($manager){
                            $customer->manager_id = $manager->id;
                            $customer->save();
                        }
                    }
                }
            }
        }
    }
    public function actionRunPhp(){
        $path = './matched.json';
        $jsonString = file_get_contents($path);
        $jsonData = json_decode($jsonString, true);
        foreach ($jsonData as $key => $row){
            if($row['matched'] == 'NO'){
                $user = User::findOne(['id' => $row['PS_ID']]);
                if($user && $user->manager_id){
                    $manager = $user->manager;
                    if($manager){
                        $jsonData[$key]['manager_first_name'] = $manager->first_name;
                        $jsonData[$key]['manager_last_name'] = $manager->last_name;
                    }
                }
            }
            $request = $this->checkGuid($row['1C ID']);
            if($request->status === 'success'){
                $res = json_decode($request->response,true);
                if($res){
                    if($res['contacts'][0]
                        && array_key_exists('contact-guid',$res['contacts'][0])
                        && array_key_exists('owner-guid',$res['contacts'][0])){
                        $user = User::findOne(['id' => $row['PS_ID']]);
                        if($user){
                            $user->contact_guid = $res['contacts'][0]['contact-guid'];
                            $user->owner_guid = $res['contacts'][0]['owner-guid'];
                            $user->one_c_status = User::ONE_C_STATUS_EXIST;
                            $user->save();
                        }
                    }
                }
            }
        }
        file_put_contents('./matched.json', json_encode($jsonData,JSON_UNESCAPED_UNICODE));
    }
}