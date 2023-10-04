<?php

namespace app\modules\user\controllers\api;

use app\modules\project\models\File;
use app\modules\project\models\forms\FileForm;
use app\modules\project\models\Project;
use app\modules\user\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\HttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\UploadedFile;

class ExternalController extends \yii\base\Controller
{
    private $secondary_border = ['i', 'd', 'e', 'n', 't', 'f', 'r'];
    private $main_border = ['s', 'e', 'c', 'u', 'r', 'i', 't', 'y', 'p', 'o', 'j'];
    public function makeNumberDoubleLength($num){
        return $num > 9 ? $num : '0'.$num ;
    }
    public function specificSymbol($secondary = false){
        if($secondary){
            return $this->secondary_border[rand(0,count($this->secondary_border) - 1)];
        }
        return $this->main_border[rand(0,count($this->main_border) - 1)];
    }
    public function encodeID($id){
        // id of file
        $id = strval($id);
        // difference between id and id-in-link
        $id_diff = $this->makeNumberDoubleLength(rand(0,99));
        // id + id_diff
        $changedId = intval($id) + intval($id_diff);
        // random hash before main hash
        $hash1 = Yii::$app->security->generateRandomString(9);
        // length of random hash before id
        $random_trash_length = rand(1,9);
        // length of changed id
        $id_length = $this->makeNumberDoubleLength(strlen($changedId));
        // random hash before id
        $hash2 = Yii::$app->security->generateRandomString($random_trash_length);


        $token = $hash1.$random_trash_length.$id_length.$id_diff.$hash2;
        $token.= $this->specificSymbol(true);
        for($i = 0;$i < $id_length; $i ++) {
            $token .= strval($changedId)[$i] . $this->specificSymbol(true);
        }
        $token.=Yii::$app->security->generateRandomString(rand(5,15));
        $token1 = $token;
        for($i = 0; $i < strlen($token); $i += 4){
            $token = substr_replace($token, $this->specificSymbol(), $i, 0);
        }
        return $token;
    }
    public function decodeID($token){
        for ($i = 0; $i < strlen($token); $i +=3){
            if(!in_array($token[$i],$this->main_border)){
                return false;
            }
            $token = substr_replace($token, '', $i, 1);
        }
        $token = substr($token, 9);
        $random_trash_length = intval($token[0]);
        $id_length = intval(substr($token, 1,2));
        $id_diff = intval(substr($token, 3,2));
        if($random_trash_length && $id_length && $id_diff){
            $token = substr($token, 5 + $random_trash_length);
            for ($i = 0; $i < $id_length; $i++){
                if(!in_array($token[$i],$this->secondary_border)){
                    return false;
                }
                $token = substr_replace($token, '', $i, 1);
            }
            $token = substr($token, 0,4);
            $token = intval($token);
            $token -= $id_diff;
            return $token;
        }
        return false;

    }
    /**
     * returns all users from PS to 1c by their role
     * @return array|string[]|void|\yii\db\ActiveRecord[]
     */
    public function actionGetUsers()
    {
        $query_role = Yii::$app->request->getQueryParam('role');
        if($query_role){
            $role = $this->getQueryRole($query_role);
            if($role){
                return User::find()
                    ->where(['role' => $role])
                    ->orderBy('id')
                    ->all();
            }
            return ['status' => 'error','message' => 'No Such Role as '.$query_role];
        }else{
            return User::find()
                ->orderBy('id')
                ->all();
        }

    }

    /**
     * @param $role
     * @return false|int
     */
    public function getQueryRole($role){
        switch ($role){
            case 'customer':
                return User::ROLE_CUSTOMER;
            case 'designer':
                return User::ROLE_DESIGNER;
            case 'admin':
                return User::ROLE_ADMIN;
            case 'manager':
                return User::ROLE_MANAGER;
        }
        return false;
    }

    /**
     * returns list of files attached to project by projects id
     * @return array|string[]
     */
    public function actionGetFiles(){
        $id = Yii::$app->request->getQueryParam('projectId');
        if(!$id){
            return [
                'status' => 'error',
                'message' => 'please specify `id`'
            ];
        }
        $project = Project::findOne($id);
        if (!$project) {
            return [
                'status' => 'error',
                'message' => 'Project not found by `id` - '.$id
            ];
        }
        $fileUrls = [];
        foreach ($project->files as $file){
            $fileUrls[] = [
                'link' => Yii::$app->urlManager
                    ->createAbsoluteUrl('api/external/projects/'.$project->id.'/files/'.$file->id)
                    .'?filename='.str_replace(' ', '_', $file->filename)
                    .'&token='.$this->encodeID($file->id),
                'type' => $file->type,
                'date' => date('d.m.Y',$file->created_at),
                'file_name' => $file->filename,
                'one_c_guid' => $file->one_c_guid
            ];
        }
        return $fileUrls;
    }


    /**
     * downloads projects file by project id and file id
     * @return string[]|\yii\console\Response|\yii\web\Response\
     */
    public function actionDownloadFile(){
        $projectId = Yii::$app->request->getQueryParam('projectId');
        $id = Yii::$app->request->getQueryParam('id');
        $token = Yii::$app->request->getQueryParam('token');
        if(!$token || $id != $this->decodeID($token)){
            throw new UnauthorizedHttpException('Unauthorized');
        }
        $project = Project::findOne($projectId);
        if (!$project) {
            return [
                'status' => 'error',
                'message' => 'Project not found by `id` - '.$projectId
            ];
        }
        $file = $project->handler->findFileById($id);
        if (!$file) {
            throw new NotFoundHttpException(Yii::t('project', 'File not found.'));
        }
        return Yii::$app->response->sendFile($project->getFileFolder() . '/' . $file->id, $file->filename);
    }
    /**
     * @return array[]
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                    ],
                ],
                'denyCallback' => function() {
                    throw new UnauthorizedHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
                }
            ],
        ];

    }

    /**
     * adds specification file to project
     * @return
     */
    public function actionAddSpecification()
    {
        $id = Yii::$app->request->getBodyParam('ps_id');
        $specification_guid = Yii::$app->request->getBodyParam('specification_guid');
        $project_guid = Yii::$app->request->getBodyParam('project_guid');
        if(!$project_guid){
            throw new HttpException(400,'please specify `project_guid`');
        }
        $project = Project::findProjectByOneCGuid($project_guid);
        if (!$project) {
            throw new HttpException(404,'Project not found by `guid` - '.$project_guid);
        }

        $status = Project::STATUS_SPECIFICATION_GRANTED;
        if ($project->handler->canUpdateStatus($status)&& $project->handler->updateStatus($status,$specification_guid)) {
            return [
                'status' => 'success',
                'message' => 'specification saved'
            ];
        }
        throw new HttpException(500,'Something went wrong, specification not saved');

    }

    public function actionAssignManager(){
        $contact_guid = Yii::$app->request->getBodyParam('contact_guid');
        $guid_manager = Yii::$app->request->getBodyParam('guid_manager');
        if (!$contact_guid) {
            throw new HttpException(400,'please specify `contact_guid`');
        }
        if (!$guid_manager) {
            throw new HttpException(400,'please specify `guid_manager`');
        }
        $customer = User::findIdentityByContactGuid($contact_guid);
        if (!$customer) {
            throw new HttpException(404,'User not found by `guid` - '.$contact_guid);
        }
        $manager = User::findIdentityByContactGuid($guid_manager);
        if (!$manager) {
            throw new HttpException(404,'Manager not found by `guid` - '.$guid_manager);
        }
        if($customer->handler->updateManager($manager->id)){
            return [
                'status' => 'success',
                'message' => 'manager successfully assigned'
            ];
        }else{
            throw new HttpException(500,'Something went wrong');
        }
    }


    public function actionDeclineProject(){
        $project_guid = Yii::$app->request->getBodyParam('project_guid');
        if (!$project_guid) {
            throw new HttpException(400,'please specify `project_guid`');
        }
        $project = Project::findProjectByOneCGuid($project_guid);
        if (!$project) {
            throw new HttpException(404,'Project not found by `guid` - '.$project_guid);
        }
        $status = Project::STATUS_REJECTED;
        if ($project->handler->canUpdateStatus($status)&& $project->handler->updateStatus($status)) {
            return [
                'status' => 'success',
                'message' => 'Project declined'
            ];
        }else{
            throw new HttpException(500,'Something went wrong');
        }
    }

    public function actionFinishProject() {
        $project_guid = Yii::$app->request->getBodyParam('project_guid');
        if (!$project_guid) {
            throw new HttpException(400,'please specify `project_guid`');
        }
        $project = Project::findProjectByOneCGuid($project_guid);
        if (!$project) {
            throw new HttpException(404,'Project not found by `guid` - '.$project_guid);
        }
        $status = Project::STATUS_FINISHED;
        if ($project->handler->canUpdateStatus($status)&& $project->handler->updateStatus($status)) {
            return [
                'status' => 'success',
                'message' => 'Project finished'
            ];
        }else{
            throw new HttpException(500,'Something went wrong');
        }
    }

    public function actionAssignDesigner(){
        $guid_designer = Yii::$app->request->getBodyParam('guid_engineer');
        $project_guid = Yii::$app->request->getBodyParam('project_guid');
        $project_id = Yii::$app->request->getBodyParam('ps_id');
        if (!$guid_designer) {
            throw new HttpException(400,'please specify `guid_engineer`');
        }
        if (!$project_guid) {
            throw new HttpException(400,'please specify `project_guid`');
        }
        $project = Project::findProjectByOneCGuid($project_guid);
        if (!$project) {
            throw new HttpException(404,'Project not found by `guid` - '.$project_guid);
        }
        $designer = User::findIdentityByContactGuid($guid_designer);
        if (!$designer) {
            throw new HttpException(404,'Designer not found by `guid` - '.$guid_designer);
        }

        if($project->handler->setDesigner($designer->id,strtotime("+7 day", time()))){
            return [
                'status' => 'success',
                'message' => 'designer successfully assigned'
            ];
        }else{
            throw new HttpException(500,'Something went wrong');
        }
    }
    /**
     * @return array|string[]
     */
    public function actionGetHistory(){
        $id = Yii::$app->request->getQueryParam('projectId');
        if(!$id){
            throw new HttpException(400,'please specify `id`');
        }
        $project = Project::findOne($id);
        if (!$project) {
            throw new HttpException(404,'Project not found by `id` - '.$id);
        }
        return $project->history;
    }

}
