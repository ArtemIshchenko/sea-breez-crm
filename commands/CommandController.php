<?php

namespace app\commands;

use app\modules\project\models\Project;
use app\modules\user\models\forms\GuidForm;
use app\modules\user\models\User;
use linslin\yii2\curl\Curl;
use Yii;
use yii\console\Controller;

class CommandController extends Controller
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
    public function actionAllProjects(){
        $projects = Project::find()->all();
        $FINAL = [];
        $statuses = [
            '',
            'создан',
            'отправлен',
            'возвращен',
            'отклонен',
            'проектирован',
            'спецификация предоставлен',
            'спецификация принята',
            'отменен',
            'закончен'
        ];
        foreach ($projects as $key => $project){
            $FINAL[] = [
                "id" => $project->id,
                "first_name" => $project->author->first_name,
                "last_name" => $project->author->last_name,
                "company" => $project->author->company,
                "manager_id" => $project->author->manager_id,
                "manager_first_name" => $project->author->manager ? $project->author->manager->first_name : '',
                "manager_last_name" => $project->author->manager ? $project->author->manager->last_name : '',
                "one_c_guid" => $project->one_c_guid,
                "user_id" => $project->user_id,
                "designer_id" => $project->designer_id,
                "title" => $project->title,
                "address" => $project->address,
                "coordinates" => $project->coordinates,
                "date" => date('d.m.Y H:s:i',$project->date),
                "status" => $statuses[$project->status],
                "client" => $project->client,
                "auction_link" => $project->auction_link,
                "delivery_conditions" => $project->delivery_conditions,
                "subcontractor" => $project->subcontractor,
                "revision_description" => $project->revision_description,
                "development_prospects" => $project->development_prospects,
                "status_message" => $project->status_message,
                "created_at" => date('d.m.Y H:s:i',$project->created_at),
                "updated_at" => date('d.m.Y H:s:i',$project->updated_at),
                "designing_deadline" => date('d.m.Y H:s:i',$project->designing_deadline)
            ];
        }
        file_put_contents('./projects.json', json_encode($FINAL,JSON_UNESCAPED_UNICODE));
    }
}