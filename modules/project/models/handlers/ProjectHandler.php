<?php

namespace app\modules\project\models\handlers;

use app\modules\user\helpers\UserHelper;
use app\modules\user\models\handlers\UserHandler;
use linslin\yii2\curl\Curl;
use phpDocumentor\Reflection\Types\Object_;
use Yii;
use yii\db\Query;
use yii\web\UnprocessableEntityHttpException;
use yii\web\UploadedFile;
use yii\helpers\{Html, HtmlPurifier, FileHelper, Json};
use app\components\Handler;
use app\modules\project\models\{Project, File, History, Comment};
use app\modules\gear\models\Gear;
use app\modules\project\models\forms\{ProjectForm, StatusMessageForm, FileForm};
use app\modules\user\models\User;

class ProjectHandler extends Handler
{

    /**
     * Creates new project.
     * @return bool If new project created.
     */
    public function create() {
        $this->record->status = Project::STATUS_CREATED;
        if ($this->record->save()) {
            $this->addToHistory('Проект создан', 'Название: ' . $this->record->title . '.');
            return true;
        }
        return false;
    }

    /**
     * Updates new project.
     * @return bool If new project created.
     */
    public function update() {
        if (empty($this->record->getDirtyAttributes())) {
            $this->record->updated_at = time();
        }
        return $this->record->save();
    }



    /**
     * Saves uploaded project files.
     * @param UploadedFile[] $files
     * @param int $type
     * @return bool If saved
     */
    public function saveFiles(array $uploaded, $type = File::TYPE_GENERAL, $guid = null, $status = 0, $imported = 0) {
        if ($type == File::TYPE_SPECIFICATION || $type == File::TYPE_TECHNICAL_TASK) {
            $uploaded = $uploaded[0];
            $currentVersionQuery = File::find()
                ->where(['project_id' => $this->record->id, 'type' => $type]);

            if ($type == File::TYPE_SPECIFICATION) {
                $currentVersionQuery->andWhere(['status' => File::STATUS_SPECIFICATION_RETURNED]);
                if ($status == 0) {
                    $status = File::STATUS_SPECIFICATION_GRANTED;
                }
            }
            $currentVersion = $currentVersionQuery->max('version');

            $version = 1;
            if ($currentVersion) {
                $version = $currentVersion + 1;
            }

            $file = Yii::createObject([
                'class' => File::class,
                'filename' => $uploaded->name,
                'type' => $type,
                'version' => $version,
                'status' => $status,
                'imported' => $imported,
                'one_c_guid' => $guid
            ]);
            try {
                $this->record->link('files', $file);
            } catch (\Exception $e) {
                Yii::error($e->getMessage());
                return false;
            }
            if (!$file->handler->upload($uploaded)) {
                Yii::error("Could not move file to uploads folder {$this->record->fileFolder}. File id - {$file->id}.");
                $file->delete();
                return false;
            }
            $this->addToHistory(
                'Загрузка файла', 'Имя файла: ' . $file->filename . '. Тип файла: ' . Yii::t('project', $type) . '. Версия файла: ' . $file->version,
                ['fileId' => $file->id]
            );
        } else {
            foreach ($uploaded as $u) {
                $file = Yii::createObject([
                    'class' => File::class,
                    'filename' => $u->name,
                    'type' => $type,
                ]);
                try {
                    $this->record->link('files', $file);
                } catch (\Exception $e) {
                    Yii::error($e->getMessage());
                    return false;
                }
                if (!$file->handler->upload($u)) {
                    Yii::error("Could not move file to uploads folder {$this->record->fileFolder}. File id - {$file->id}.");
                    $file->delete();
                    return false;
                }
                $this->addToHistory(
                    'Загрузка файла', 'Имя файла: ' . $file->filename . '. Тип файла: ' . Yii::t('project', $type),
                    ['fileId' => $file->id]
                );
            }
        }
        return true;
    }

    /**
     * Returns project's file by given id.
     * @param integer $id
     * @return File|null
     */
    public function findFileById($id) {
        foreach ($this->record->files as $file) {
            if ($file->id == $id) {
                return $file;
            }
        }
        return null;
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


    public function requestToSpecificationParser($filePath, $fileId, $filename) {
        $result = [];
        $mimeType = 'application/vnd.ms-excel';
        if (preg_match('/.*\.xls$/i', $filename)) {
            $mimeType = 'application/vnd.ms-excel';
        } elseif (preg_match('/.*\.xlsx$/i', $filename)) {
            $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        } elseif (preg_match('/.*\.txt$/i', $filename)) {
            $mimeType = 'text/plain';
        } elseif (preg_match('/.*\.csv$/i', $filename)) {
            $mimeType = 'text/csv';
        }

        $curl = new Curl();
        $file = new \CURLFile($filePath, $mimeType);

        $data = [
            "file" => $file
        ];
        $response = $curl
            ->setRawPostData($data)
            ->setHeaders([
                'Content-Type' => 'multipart/form-data',
                'Authorization' => 'Bearer ' . Yii::$app->params['SPECIFICATION_PARSER']['bearerToken'],
                'Client-id' => Yii::$app->params['SPECIFICATION_PARSER']['clientId']
            ])
            ->post(Yii::$app->params['SPECIFICATION_PARSER']['baseUrl'] . '/upload_file_specification');

        if ($response) {
            $resp = json_decode($response);
            if (isset($resp->sheets)) {
                $sheets = [];
                switch($mimeType) {
                    case 'application/vnd.ms-excel':
                    case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                    case 'text/csv':
                    default:
                        foreach ($resp->sheets as $sheet) {
                            $rows = [];
                            $startNum = 0;
                            $numCol = 0;
                            $notEmptyColumns = [];
                            $excludeRows = [];
                            if ($sheet && $sheet->rows) {
                                foreach ($sheet->rows as $num => $row) {
                                    $numCurCol = 0;
                                    $hasNumValue = false;
                                    foreach ($row as $item) {
                                        $val = str_replace([' ', ','], ['', '.'], $item->origin_cell_value);
                                        if (!is_numeric($val) && !empty($item->origin_cell_value)) {
                                            ++$numCurCol;
                                        }
                                        if (is_numeric($val)) {
                                            $hasNumValue = true;
                                        }
                                    }
                                    if ($numCurCol > $numCol) {
                                        $numCol = $numCurCol;
                                        if (!$hasNumValue) {
                                            $startNum = $num;
                                        }
                                    }
                                    if (!$hasNumValue) {
                                        //$excludeRows[] = $num;
                                    }
                                }
                                $key = array_search($startNum, $excludeRows);
                                if ($key !== false) {
                                    unset($excludeRows[$key]);
                                }
                                foreach ($sheet->rows as $num => $row) {
                                    if ($num >= $startNum && !in_array($num, $excludeRows)) {
                                        $rowResult = [];
                                        foreach ($row as $itemNum => $item) {
                                            $rowResult[] = [
                                                'origin_cell_value' => $item->origin_cell_value,
                                                'col_num' => $itemNum + 1,
                                            ];
                                            if (!empty($item->origin_cell_value)) {
                                                $notEmptyColumns[$itemNum] = true;
                                            }
                                        }
                                        $rows[] = $rowResult;
                                    }
                                }
                                $rowsFiltered = $rows;
                                if (!empty($rows) && !empty($notEmptyColumns)) {
                                    $rowsFiltered = [];
                                    foreach ($rows as $row) {
                                        $rowResult = [];
                                        foreach ($row as $num => $col) {
                                            if (isset($notEmptyColumns[$num])) {
                                                $rowResult[] = $col;
                                            }
                                        }
                                        $rowsFiltered[] = $rowResult;
                                    }
                                }
                                $sheets[] = [
                                    'rows' => $rowsFiltered,
                                    'sheet_num' => $sheet->sheet_num,
                                ];
                            }
                        }
                        break;
                    case 'text/plain':
                        $rows = [];
                        foreach ($resp->sheets as $sheet) {
                            $rowResult = [];
                            foreach ($sheet->headers as $itemNum => $header) {
                                $rowResult[] = [
                                    'origin_cell_value' => $header,
                                    'col_num' => $itemNum + 1,
                                ];
                            }
                            $rows[] = $rowResult;

                            foreach ($sheet->rows as $row) {
                                $rowResult = [];
                                foreach ($row as $itemNum => $item) {
                                    $value = '';
                                    switch ($item->cell_type) {
                                        case 'model':
                                            $value = $item->model_to_find;
                                            break;
                                        case 'brand':
                                            $value = $item->brand;
                                            break;
                                        case 'quantity':
                                            $value = $item->quantity;
                                            break;
                                    }
                                    $rowResult[] = [
                                        'origin_cell_value' => $value,
                                        'cell_type' => $item->cell_type,
                                        'col_num' => $itemNum + 1,
                                    ];
                                }
                                $rows[] = $rowResult;
                            }
                            $sheets[] = [
                                'rows' => $rows,
                                'sheet_num' => $sheet->sheet_num
                            ];
                        }
                        break;
                }
                $result = [
                    'sheets' => $sheets,
                    'job_id' => $resp->job_id,
                    'file_id' => $fileId,
                ];
            }
        }

        return $result;
    }

    public function requestToSpecificationParserTune($fileId, $filename, $jobId, $tuneParams) {
        $mimeType = 'application/vnd.ms-excel';
        if (preg_match('/.*\.xls$/i', $filename)) {
            $mimeType = 'application/vnd.ms-excel';
        } elseif (preg_match('/.*\.xlsx$/i', $filename)) {
            $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        } elseif (preg_match('/.*\.txt$/i', $filename)) {
            $mimeType = 'text/plain';
        } elseif (preg_match('/.*\.csv$/i', $filename)) {
            $mimeType = 'text/csv';
        }

        $excludeFirstRow = isset($tuneParams['row_with_header']) && $tuneParams['row_with_header'] == 1;

        foreach ($tuneParams as &$item) {
            $item = (integer) $item;
            if (isset($item['row_with_header'])) {
                unset($item);
            }
        }
        unset($item);

        $data = [
            'job_id' => $jobId,
            'sheets' => [
                $tuneParams
            ]
        ];
        $curl = new Curl();
        $requestResponse = $curl
            ->setRequestBody(json_encode($data))
            ->setHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . Yii::$app->params['SPECIFICATION_PARSER']['bearerToken'],
                'Client-id' => Yii::$app->params['SPECIFICATION_PARSER']['clientId']
            ])
            ->get(Yii::$app->params['SPECIFICATION_PARSER']['baseUrl'] . '/tune_specification');

        $response = json_decode($requestResponse, true);
        if ($response && isset($response['job_id'])) {
            $file = File::findOne($fileId);
            if ($file) {
                $sheets = [];
                if (isset($response['sheets']) && $response['sheets']) {
                    switch($mimeType) {
                        case 'application/vnd.ms-excel':
                        case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                        case 'text/csv':
                        default:
                            foreach ($response['sheets'] as $sheet) {
                                $rows = [];
                                $goods = [];
                                $startNum = 0;
                                $numCol = 0;
                                $notEmptyColumns = [];
                                $excludeRows = [];
                                foreach ($sheet['rows'] as $num => $row) {
                                    $numCurCol = 0;
                                    $hasNumValue = false;
                                    foreach ($row as $item) {
                                        $val = str_replace([' ', ','], ['', '.'], $item['origin_cell_value']);
                                        if (!is_numeric($val) && !empty($item['origin_cell_value'])) {
                                            ++$numCurCol;
                                        }
                                        if (is_numeric($val)) {
                                            $hasNumValue = true;
                                        }
                                    }
                                    if ($numCurCol > $numCol) {
                                        $numCol = $numCurCol;
                                        if (!$hasNumValue) {
                                            $startNum = $num;
                                        }
                                    }
                                    if (!$hasNumValue) {
                                        //$excludeRows[] = $num;
                                    }
                                }
                                $key = array_search($startNum, $excludeRows);
                                if ($key !== false) {
                                    unset($excludeRows[$key]);
                                }
                                foreach ($sheet['rows'] as $num => $row) {
                                    if ($num >= $startNum && !in_array($num, $excludeRows)) {
                                        $rowResult = [];
                                        foreach ($row as $itemNum => $item) {
                                            $rowResult[] = $item;
                                            if (!empty($item['origin_cell_value'])) {
                                                $notEmptyColumns[$itemNum] = true;
                                            }
                                        }
                                        $rows[] = $rowResult;
                                    }
                                }
                                $rowsFiltered = $rows;
                                if (!empty($rows) && !empty($notEmptyColumns)) {
                                    $rowsFiltered = [];
                                    foreach ($rows as $row) {
                                        $rowResult = [];
                                        foreach ($row as $num => $col) {
                                            if (isset($notEmptyColumns[$num])) {
                                                $rowResult[] = $col;
                                            }
                                        }
                                        $rowsFiltered[] = $rowResult;
                                    }
                                }

                                foreach ($rowsFiltered as $row) {
                                    $codes = [];
                                    $quantity = 0;
                                    $name = '';
                                    $isModel = false;
                                    $isModelValueEmpty = false;
                                    foreach ($row as $item) {
                                        switch ($item['cell_type']) {
                                            case 'model':
                                                if (isset($item['model_list']) && !empty($item['model_list'])) {
                                                    foreach ($item['model_list'] as $model) {
                                                        $codes[] = $model['code'];
                                                    }
                                                }
                                                $name = $item['origin_cell_value'];
                                                $isModel = true;
                                                $isModelValueEmpty = empty($name);
                                                break;
                                            case 'quantity':
                                                if (isset($item['quantity'])) {
                                                    $quantity = $item['quantity'];
                                                }
                                                break;
                                        }
                                    }
                                    if (!$isModel || $isModelValueEmpty) {
                                        continue;
                                    }
                                    $goods[] = [
                                        'codes' => $codes,
                                        'name' => $name,
                                        'qty' => $quantity,
                                    ];
                                }
                                $sheets[] = [
                                    'sheet_num' => $sheet['sheet_num'],
                                    'goods' => $goods
                                ];
                            }
                            break;
                        case 'text/plain':
                            foreach ($response['sheets'] as $sheet) {
                                $goods = [];
                                foreach ($sheet['rows'] as $row) {
                                    $codes = [];
                                    $quantity = 0;
                                    $name = '';
                                    $isModel = false;
                                    foreach ($row as $item) {
                                        switch ($item['cell_type']) {
                                            case 'model':
                                                if (isset($item['model_list']) && !empty($item['model_list'])) {
                                                    foreach ($item['model_list'] as $model) {
                                                        $codes[] = $model['code'];
                                                    }
                                                }
                                                $name = $item['origin_cell_value'];
                                                $isModel = true;
                                                break;
                                            case 'quantity':
                                                if (isset($item['quantity'])) {
                                                    $quantity = $item['quantity'];
                                                }
                                                break;
                                        }
                                    }
                                    if (!$isModel) {
                                        continue;
                                    }
                                    $goods[] = [
                                        'codes' => $codes,
                                        'name' => $name,
                                        'qty' => $quantity,
                                    ];
                                }
                                $sheets[] = [
                                    'sheet_num' => $sheet['sheet_num'],
                                    'goods' => $goods
                                ];
                            }
                            break;
                    }


                    $specificationData = [
                        'job_id' => $response['job_id'],
                        'sheets' => $sheets,
                        'exclude_first_row' => $excludeFirstRow,
                    ];
                    $file->specification_data = json_encode($specificationData);
                    $file->imported = File::IMPORTED;
                    $file->save();
                }
            }
        }

        return $response;
    }

    /**
     * sends users response to specification
     * @return array|false|string[]
     * @throws \Exception
     */
    public function sendAnswerTo1C($accepted, $specificationGuid, $message = null){
        $project = $this->record;
        if($project->author->provider){
            $body = [
                "specification_guid"=> $specificationGuid,
                "project_guid"=> $project->one_c_guid,
                "accepted" => $accepted,
                "desc" => $message
            ];
            $request = $this->requestTo1C($body,Yii::$app->params['ONE_C_INTEGRATION']['specification_url']);
            switch ($request->code){
                case 200:
                    return ['status' => 'success','response' => $request->response];
                case 400:
                    return ['status' => 'error','error' => '400 Bad Request'];
                case 401:
                    return ['status' => 'error','error' => '401 Unauthorized'];
                default:
                    return ['status' => 'error','error' => $request->code, 'response' => $request->response];
            }
        }

        return false;
    }
    /**
     * sends users response to specification
     * @return array|false|string[]
     * @throws \Exception
     */
    public function sendStatusTo1C($status, $reason){
        $project = $this->record;
        if($project->author->provider){
            $body = [
                "project_guid"=> $project->one_c_guid,
                "status" => $status,
                'reason' => $reason
            ];
            $request = $this->requestTo1C($body,Yii::$app->params['ONE_C_INTEGRATION']['project_status_url']);
            switch ($request->code){
                case 200:
                    return ['status' => 'success','response' => $request->response];
                case 400:
                    return ['status' => 'error','error' => '400 Bad Request'];
                case 401:
                    return ['status' => 'error','error' => '401 Unauthorized'];
                default:
                    return ['status' => 'error','error' => $request->code, 'response' => $request->response];
            }
        }

        return false;
    }
    public function notifyMissingGuid(){
        $project = $this->record;
        if($project->author->provider && !$project->author->contact_guid){
//            Yii::$app->mailer
//                ->compose([
//                    'html' => 'projectSentWithoutGuid-html',
//                    'text' => 'projectSentWithoutGuid-text'
//                ], [
//                    'toRole' => 'admin',
//                    'project' => $this->record
//                ])
//                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
//                ->setSubject(Yii::t('project', 'Project sent without guid'))
//                ->send();
            $files = [];
            foreach ($project->files as $file) {
                $files[] = $file->filename;
            }
            $toRole = 'admin';
            $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params[$toRole . 'AppUrl'], '#' => '/projects/' . $project->id . '/']);
            $data = [
                'firstName' => Html::encode($project->author->first_name),
                'lastName' => Html::encode($project->author->last_name),
                'middleName' => Html::encode($project->author->middle_name),
                'company' => Html::encode($project->author->company),
                'projectTitle' => Html::encode($project->title),
                'projectDate' => Yii::$app->formatter->asDate($project->date, 'long'),
                'customer' => Html::encode($project->client ?: 'Нет данных'),
                'subcontractor' => Html::encode($project->subcontractor ?: 'Нет данных'),
                'revisionDescription' => $project->revision_description ? 'да' : 'нет',
                'developmentProspects' => $project->development_prospects ? 'да' : 'нет',
                'files' => $files ? Html::encode(implode(', ', $files)) : 'Нет данных',
                'subject' => Yii::t('project', 'Project sent without guid'),
            ];
            if (!empty($emails)) {
                foreach ($emails as $email) {
                    $project->author->handler->generateEvent(
                        $email,
                        UserHandler::EVENT_TYPE_KEY['projectSentWithoutGuid'],
                        $url,
                        $data
                    );
                }
            }
        }
    }
    /**
     * sends project info ti 1c
     * @return array|false|string[]
     */
    public function sendProjectTo1C(){
        $project = $this->record;
        if($project->author->provider){
            $body = null;
                $body = [
                    "ps_id" => $project->id,
                    "created_at" => date("d.m.Y H:i:s", $project->created_at),
                    "partner_guid" => "a07c5196-c4b0-11ed-8bd3-000c296ed57e",
                    "contact_guid" => "c36bdd92-c4b0-11ed-8bd3-000c296ed57e",
                    "sales_manager_guid" => "cc7ebc96-df2e-11eb-8265-000c29081ad9" ,
                    "project_name" => $project->title,
                    "terms_of_delivery" => $project->delivery_conditions,
                    "description" => $project->revision_description,
                    "deadline" => date("d.m.Y H:i:s", $project->date),
                    "tender_link" => $project->auction_link,
                    "sub_contractor" => $project->subcontractor,
                    "contractor" => $project->client,
                    "additional_info" => $project->development_prospects,
                    "installation_place" => $project->address,
                ];
            }
            $body['provider'] = $project->author->provider;

            $request = $this->requestTo1C($body,Yii::$app->params['ONE_C_INTEGRATION']['project_url']);
            switch ($request->code){
                case 200:
                    $res = json_decode($request->response);
                    $this->record->one_c_guid = $res->project_guid;
                    if($this->record->save()){
                        return ['status' => 'success','response' => $res, 'guid' => $res->project_guid];
                    }else{
                        return ['status' => 'error','error' => 'Something went wrong'];
                    }
                case 400:
                    return ['status' => 'error','error' => '400 Bad Request'];
                case 401:
                    return ['status' => 'error','error' => '401 Unauthorized'];
                default:
                    return ['status' => 'error','error' => $request->code];
            }
        return false;
    }

    public function sendSpecDataToParser() {
        $file = File::find()
            ->where(['project_id' => $this->record->id])
            ->andWhere(['type' => File::TYPE_SPECIFICATION])
            ->andWhere(['status' => File::STATUS_SPECIFICATION_IMPORTED])
            ->andWhere(['imported' => File::IMPORTED])
            ->andWhere(['deleted_at' => null])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();
        if ($file && $file->specification_data) {
            $specificationData = json_decode($file->specification_data, true);
            if (isset($specificationData['exclude_first_row'])) {
                if ($specificationData['exclude_first_row'] && !empty($specificationData['sheets'][0]['goods'])) {
                    array_shift($specificationData['sheets'][0]['goods']);
                }
                unset($specificationData['exclude_first_row']);
            }
            $data = array_merge(
                ['guid' => $this->record->one_c_guid],
                $specificationData
            );
        } else {
            $data = [
                'guid' => $this->record->one_c_guid,
                'job_id' => '',
                'sheets' => [
                    'sheet_num' => 0,
                    'goods' => []
                ]
            ];
        }

        $request = $this->requestTo1C($data,Yii::$app->params['ONE_C_INTEGRATION']['parser_url']);
        switch ($request->code){
            case 200:
                return ['status' => 'success'];
            case 400:
                return ['status' => 'error','error' => '400 Bad Request'];
            case 401:
                return ['status' => 'error','error' => '401 Unauthorized'];
            default:
                return ['status' => 'error','error' => $request->code];
        }

    }

    /**
     * Checks if project can be updated with given status.
     * @param int $status status code.
     * @throws UnprocessableEntityHttpException if new status is inappropriate.
     * @return bool
     */
    public function canUpdateStatus($status) {
        $wrongUserRoleError = new UnprocessableEntityHttpException(Yii::t('project', 'You don\'t have permissions to set this status to project.'));
        $wrongOrderError = new UnprocessableEntityHttpException(Yii::t('project', 'Wrong order of status update.'));
        $existsNotReturned = File::find()->where(['project_id' => $this->record->id])
            ->andWhere(['type' => File::TYPE_SPECIFICATION])
            ->andWhere(['<>', 'status', File::STATUS_SPECIFICATION_RETURNED])
            ->exists();
        $specificationId = Yii::$app->request->getBodyParam('specification_id');
        switch ($status) {
            case Project::STATUS_SENT:
                // check user
                if (!Yii::$app->user->isCustomer())
                    throw $wrongUserRoleError;
                // check order
                if (!in_array($this->record->status, [Project::STATUS_CREATED, Project::STATUS_RETURNED, Project::STATUS_SPECIFICATION_GRANTED, Project::STATUS_SENT]) || ($this->record->status == Project::STATUS_SENT && !$specificationId))
                    throw $wrongOrderError;
                if ($this->record->status == Project::STATUS_SPECIFICATION_GRANTED && $this->record->designer_id)
                    throw $wrongOrderError;
                // check project integrity
                $form = new ProjectForm([
                    'record' => $this->record,
                    'scenario' => ProjectForm::SCENARIO_SEND
                ]);
                if (!$form->validate()) {
                    throw new UnprocessableEntityHttpException(implode(' ', $form->getErrorSummary(true)));
                }
                break;

            case Project::STATUS_CANCELED:
                // check user
                if (!Yii::$app->user->isCustomer())
                    throw $wrongUserRoleError;
                // check order
                if (in_array($this->record->status, [Project::STATUS_CREATED, Project::STATUS_CANCELED, Project::STATUS_FINISHED, Project::STATUS_REJECTED]))
                    throw $wrongOrderError;
                break;

            case Project::STATUS_DESIGNING:
                // check user
                if (Yii::$app->user->isDesigner())
                    throw $wrongUserRoleError;
                // check order
                if (Yii::$app->user->isCustomer()) {
                    if ($this->record->status != Project::STATUS_SPECIFICATION_GRANTED || (!$this->record->designer_id && !$specificationId)) {
                        throw $wrongOrderError;
                    }
                }
                else if (($this->record->status != Project::STATUS_SENT) && !$specificationId)
                    throw $wrongOrderError;
                break;
            case Project::STATUS_RETURNED:
                // check user
                if (!Yii::$app->user->isAdmin() && !Yii::$app->user->isManager())
                    throw $wrongUserRoleError;
                // check order
                if ($this->record->status != Project::STATUS_SENT)
                    throw $wrongOrderError;
                break;
            case Project::STATUS_REJECTED:
                // check user
                if (!Yii::$app->user->isAdmin() && !Yii::$app->user->isManager() && !Yii::$app->user->isOneC())
                    throw $wrongUserRoleError;
                // check order
                if (!in_array($this->record->status, [Project::STATUS_CREATED, Project::STATUS_SENT, Project::STATUS_DESIGNING, Project::STATUS_SPECIFICATION_GRANTED, Project::STATUS_SPECIFICATION_ACCEPTED]))
                    throw $wrongOrderError;
                if ($this->record->status == Project::STATUS_CREATED) {
                    if ($this->record->date && $this->record->date >= time())
                        throw $wrongOrderError;
                    if (!$this->record->date && $this->record->updated_at >= time() - 60 * 60 * 24 * 30)
                        throw $wrongOrderError;
                }
                break;

            case Project::STATUS_SPECIFICATION_GRANTED:
                // designer grants specification
                if (Yii::$app->user->isDesigner()) {
                    if ($this->record->status != Project::STATUS_DESIGNING)
                        throw $wrongOrderError;
                }
                // admin or manager grants specification
                else if (Yii::$app->user->isAdmin() || Yii::$app->user->isManager() || Yii::$app->user->isOneC()) {
                    if (!in_array($this->record->status, [Project::STATUS_SENT, Project::STATUS_DESIGNING, Project::STATUS_SPECIFICATION_GRANTED]))
                        throw $wrongOrderError;
                }
                else {
                    throw $wrongUserRoleError;
                }
                break;

            case Project::STATUS_SPECIFICATION_ACCEPTED:
                // check user
                if (!Yii::$app->user->isCustomer())
                    throw $wrongUserRoleError;
                // check order
                if ($this->record->status != Project::STATUS_SPECIFICATION_GRANTED)
                    throw $wrongOrderError;
                break;

            case Project::STATUS_FINISHED:
                // check user
                if (!Yii::$app->user->isAdmin() && !Yii::$app->user->isManager() && !Yii::$app->user->isOneC())
                    throw $wrongUserRoleError;
                // check order for admin
                if (Yii::$app->user->isAdmin() || Yii::$app->user->isOneC()) {
                    if (!in_array($this->record->status, [
                        Project::STATUS_SPECIFICATION_ACCEPTED,
                        Project::STATUS_SENT,
                        Project::STATUS_DESIGNING,
                        Project::STATUS_SPECIFICATION_GRANTED
                    ])) {
                        throw $wrongOrderError;
                    }
                }
                if (Yii::$app->user->isManager()) {
                    if ($this->record->status != Project::STATUS_SPECIFICATION_ACCEPTED
                        && (!in_array($this->record->status, [
                            Project::STATUS_SENT,
                            Project::STATUS_DESIGNING,
                            Project::STATUS_SPECIFICATION_GRANTED
                        ]) || time() < $this->record->date)) {
                        throw $wrongOrderError;
                    }
                }
                break;

            default:
                return false;
        }
        return true;
    }

    /**
     * Get and valiadte message on status update
     * @return string
     */
    private function _processStatusMessage() {
        $form = new StatusMessageForm([
            'record' => $this->record,
            'message' => Yii::$app->request->getBodyParam('message')
        ]);
        if (!$form->validate()) {
            throw new UnprocessableEntityHttpException(implode(' ', $form->getErrorSummary(true)));
        }
        return $form->message;
    }

    /**
     * Get returned specifications guids
     * @param int $projectId
     * @return array
     */
    private function _getReturnedSpecificationsGuids($projectId) {
        $result = [];
        $returnedSpecifications = File::find()
            ->from(['pf' => 'project_file'])
            ->where(['project_id' => $projectId])
            ->andWhere(['type' => File::TYPE_SPECIFICATION])
            ->andWhere(['status' => File::STATUS_SPECIFICATION_RETURNED])
            ->andWhere(['=',
                'version', (new Query())
                    ->from(['pf1' => 'project_file'])
                    ->select('MAX(`version`)')
                    ->where(['pf1.project_id' => $projectId])
                    ->andWhere('pf1.filename = pf.filename')
                    ->andWhere(['pf1.type' => File::TYPE_SPECIFICATION])
                    ->andWhere(['pf1.status' => File::STATUS_SPECIFICATION_RETURNED])

            ])
            ->all();
        if ($returnedSpecifications) {
            foreach($returnedSpecifications as $item) {
                $result[] = $item->one_c_guid;
            }
        }

        return $result;
    }

    /**
     * Updates project status
     * @param int $status Status code
     * @return bool
     */
    public function updateStatus($status, $guid = null) {
        switch ($status) {
            case Project::STATUS_SENT:
                // Project with these name or address or customer is exists
                $projectsWithSomeData = [];
                $projWithSomeData = Project::find()
                    ->where([
                        'or',
                        [
                            'or',
                            ['like', 'title', $this->record->title],
                            ['address' => $this->record->address]
                        ],
                        ['like', 'client', $this->record->client]
                    ])
                    ->andWhere(['<>', 'id', $this->record->id])
                    ->andWhere(['not in', 'status', [Project::STATUS_FINISHED, Project::STATUS_REJECTED, Project::STATUS_CANCELED]])
                    ->asArray()
                    ->all();
                if ($projWithSomeData) {
                    foreach ($projWithSomeData as $proj) {
                        $projectsWithSomeData[] = $proj['id'];
                    }
                }
                $specificationId = Yii::$app->request->getBodyParam('specification_id');
                // Customer returning specification case
                if (($this->record->status == Project::STATUS_SPECIFICATION_GRANTED) || ($this->record->status == Project::STATUS_SENT && $specificationId)) {
                    $this->record->status = Project::STATUS_SENT;
                    $this->record->status_message = $this->_processStatusMessage();
                    if ($this->record->save()) {
                        if ($specificationId) {
                            $file = File::findOne($specificationId);
                            if ($file) {
                                $file->status = File::STATUS_SPECIFICATION_RETURNED;
                                if ($file->save()) {
                                    $existsNotReturned = File::find()->where(['project_id' => $this->record->id])
                                        ->andWhere(['type' => File::TYPE_SPECIFICATION])
                                        ->andWhere(['<>', 'status', File::STATUS_SPECIFICATION_RETURNED])
                                        ->exists();
                                    $statusMessage = $this->record->status_message;
                                    if ($existsNotReturned) {
                                        $this->addToHistory("Спецификация {$file->filename} возвращена на доработку", 'Новый статус: "отправлен".');
                                        return true;
                                    } else {
                                        $this->sendAnswerTo1C(false, $file->one_c_guid, $statusMessage);
                                        $this->record->author->handler->notifyManagement('specificationReturned', Yii::t('project', 'Project specification was denied.'), [
                                            'project' => $this->record
                                        ]);
                                        $this->addToHistory("Спецификация {$file->filename} возвращена на доработку", 'Новый статус: "отправлен", комментарий заказчика: "' . $statusMessage . '".');
                                    }
                                }
                            }
                        }
                        if (!empty($projectsWithSomeData)) {
                            $this->record->author->handler->notifyManagement('sentProjectWithSomeData', Yii::t('project', 'Project sent with same or name or contractor or address.'), [
                                'project' => $this->record,
                                'idsSimilarProjects' => $projectsWithSomeData,
                            ]);
                        }

                        return true;
                    }
                }
                // Customer sends project case
                else {
                    $this->record->status = Project::STATUS_SENT;
                    $this->record->status_message = null;
                    if ($this->record->save()) {
                        $this->sendProjectTo1C();
                        $this->sendSpecDataToParser();
                        $this->notifyMissingGuid();
                        $this->record->author->handler->notifyManagement('projectCreated', Yii::t('project', 'New project was created'), [
                            'project' => $this->record
                        ]);
                        if (!empty($projectsWithSomeData)) {
                            $this->record->author->handler->notifyManagement('sentProjectWithSomeData', Yii::t('project', 'Project sent with same or name or contractor or address.'), [
                                'project' => $this->record,
                                'idsSimilarProjects' => $projectsWithSomeData,
                            ]);
                        }
                        $this->addToHistory('Проект отправлен');
                        return true;
                    }
                }
                return false;

            case Project::STATUS_CANCELED:
                $this->record->status = Project::STATUS_CANCELED;
                $this->record->status_message = $this->_processStatusMessage();
                if ($this->record->save()) {
                    $this->sendStatusTo1C('cancel',$this->record->status_message);
                    $this->record->author->handler->notifyManagement('projectCanceled', Yii::t('project', 'Project was canceled'), [
                        'project' => $this->record
                    ]);
                    if ($this->record->designer_id) {
                        $this->record->designer->handler->notify('projectCanceled', Yii::t('project', 'Project was canceled'), [
                            'project' => $this->record,
                            'toRole' => 'designer'
                        ]);
                    }
                    $this->addToHistory('Проект отозван', 'Сообщение: "' . $this->record->status_message . '"');
                    return true;
                }
                return false;

            case Project::STATUS_DESIGNING:
                // Customer returning specification case
                $specificationId = Yii::$app->request->getBodyParam('specification_id');
                if ($specificationId) {
                    $file = File::findOne($specificationId);
                    if ($file) {
                        if (Yii::$app->user->isCustomer()) {
                            $file->status = File::STATUS_SPECIFICATION_RETURNED;
                            if ($file->save()) {
                                $existsNotReturned = File::find()->where(['project_id' => $this->record->id])
                                    ->andWhere(['type' => File::TYPE_SPECIFICATION])
                                    ->andWhere(['<>', 'status', File::STATUS_SPECIFICATION_RETURNED])
                                    ->exists();
                                $statusMassage = $this->_processStatusMessage();
                                if ($existsNotReturned) {
                                    $this->addToHistory("Спецификация {$file->filename} возвращена на доработку", 'Новый статус: "у проектировщика".');
                                    return true;
                                } else {
                                    $this->record->status = Project::STATUS_DESIGNING;
                                    $this->record->status_message = $statusMassage;
                                    if ($this->record->save()) {
                                        $this->sendAnswerTo1C(false, $file->one_c_guid, $statusMassage);
                                        $this->record->designer->handler->notify('specificationReturned', Yii::t('project', 'Project specification was denied.'), [
                                            'project' => $this->record,
                                            'toRole' => 'designer'
                                        ]);
                                        $this->record->author->handler->notifyManagement('specificationReturned', Yii::t('project', 'Project specification was denied.'), [
                                            'project' => $this->record
                                        ]);
                                        $this->addToHistory("Спецификация №{$file->filename} возвращена на доработку", 'Новый статус: "у проектировщика", комментарий заказчика: "' . $statusMassage . '".');
                                        return true;
                                    }
                                }
                            }
                        }
                    }
                }
                // Setting designer case
                else {
                    $designerId = (int) Yii::$app->request->getBodyParam('designer_id');
                    $designingDeadline = (int) Yii::$app->request->getBodyParam('designing_deadline');
                    return $this->setDesigner($designerId, $designingDeadline);
                }
                return false;

            case Project::STATUS_RETURNED:
                $this->record->status = Project::STATUS_RETURNED;
                $this->record->status_message = $this->_processStatusMessage();
                if ($this->record->save()) {
                    $this->record->author->handler->notify('projectReturned', Yii::t('project', 'Project was returned'), [
                        'project' => $this->record
                    ]);
                    $this->addToHistory('Проект возвращен на доработку', 'Сообщение: "' . $this->record->status_message . '"');
                    return true;
                }
                return false;

            case Project::STATUS_REJECTED:
                $this->record->status = Project::STATUS_REJECTED;
                $this->record->status_message = $this->_processStatusMessage();
                if ($this->record->save()) {
                    $rejectedBy = Yii::$app->request->getBodyParam('rejected_by', 'Администратором');
                    $this->record->author->handler->notify('projectRejected', Yii::t('project', 'Project was rejected'), [
                        'project' => $this->record,
                        'rejectedBy' => $rejectedBy,
                    ]);
                    $this->addToHistory('Проект отклонен', 'Сообщение: "' . $this->record->status_message . '"');
                    return true;
                }
                return false;

            case Project::STATUS_SPECIFICATION_GRANTED:
                $fileProp = Yii::$app->params['external'] ? 'document' : 'specification';
                $form = new FileForm([
                    'record' => $this->record,
                    'type' => File::TYPE_SPECIFICATION,
                    'guid' => $guid,
                    'files' => [UploadedFile::getInstanceByName($fileProp)]
                ]);
                if (!$form->save()) {
                    throw new UnprocessableEntityHttpException(Yii::t('project', 'Specification was not saved.') . implode(' ', $form->getErrorSummary(true)));
                }
                $this->record->status = Project::STATUS_SPECIFICATION_GRANTED;
                $this->record->status_message = null;
                if ($this->record->save(false, ['status'])) {
                    $this->addToHistory("Спецификация подана", 'Проектировщик: ' . Yii::$app->user->identity->first_name . ' ' . Yii::$app->user->identity->last_name);
                    $this->record->author->handler->notify('specificationSet', Yii::t('project', 'Specification was set to your project'), [
                        'project' => $this->record
                    ]);
                    return true;
                }
                return false;

            case Project::STATUS_SPECIFICATION_ACCEPTED:
                $specificationId = Yii::$app->request->getBodyParam('specification_id');
                if ($specificationId) {
                    $file = File::findOne($specificationId);
                    if ($file) {
                        $this->record->status = Project::STATUS_SPECIFICATION_ACCEPTED;
                        $this->record->status_message = null;
                        $file->status = File::STATUS_SPECIFICATION_ACCEPTED;
                        if ($this->record->save() && $file->save()) {
                            $this->sendAnswerTo1C(true, $file->one_c_guid);
                            $this->record->author->handler->notifyManagement('specificationAccepted', Yii::t('project', 'Customer accepted specification'), [
                                'project' => $this->record
                            ]);
                            $this->addToHistory('Спецификация принята');

                            return true;
                        }
                    }
                }
                return false;

            case Project::STATUS_FINISHED:
                $this->record->status = Project::STATUS_FINISHED;
                $this->record->status_message = $this->_processStatusMessage();
                if ($this->record->save()) {
                    $this->addToHistory('Проект завершен', 'Сообщение: "' . $this->record->status_message . '"');
                    return true;
                }
                return false;

            default:
                return false;
        }
    }

    /**
     * Sets designer to project
     * @param int $designerId
     * @param int $deadline
     * @return bool
     */
    public function setDesigner($designerId, $deadline) {
        if (!$designerId) {
            throw new UnprocessableEntityHttpException(Yii::t('project', 'Existing designer has to be set.'));
        }
        $designer = User::findOne(['id' => $designerId, 'role' => User::ROLE_DESIGNER]);
        if (!$designer) {
            throw new UnprocessableEntityHttpException(Yii::t('project', 'Existing designer has to be set.'));
        }
        $this->record->status = Project::STATUS_DESIGNING;
        $this->record->designer_id = $designerId;
        $this->record->designing_deadline = $deadline;
        $this->record->status_message = null;
        if ($this->record->save()) {
            $designer->handler->notify('designerAssigned', Yii::t('project', 'New project assigned to you'), [
                'project' => $this->record
            ]);
            $this->addToHistory('Проектировщик назначен', 'Проектировщик: ' . $designer->first_name . ' ' . $designer->last_name .
                ', дедлайн проектировки: ' . Yii::$app->formatter->asDate($this->record->designing_deadline) . '.');
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
    public function addToHistory($action, $additional = null, $metaData = null) {
        $story = new History([
            'action' => $action,
            'additional' => HtmlPurifier::process($additional),
            'meta' => Json::encode($metaData)
        ]);
        $this->record->link('history', $story);
    }

    /**
     * Adds comment. Comment should be purified and vaidated.
     * @param string $comment
     * @return void
     */
    public function addComment($body, $authorVisible) {
        $comment = new Comment([
            'body' => $body,
            'author_visible' => $authorVisible
        ]);
        $this->record->link('comments', $comment);
    }

    /**
     * File deleted hook
     * @param File $file
     * @return void
     */
    public function fileDeleted($file) {
        $this->addToHistory(
            'Файл удален.', 'Название файла: ' . $file->filename . '. Тип файла: ' . Yii::t('project', $file->type) . ($file->type != File::TYPE_GENERAL ? " Версия: {$file->version}." : ''),
            ['fileId' => $file->id]
        );
    }

    public function addGear($gear) {
        $this->record->link('gears', $gear);
        $this->addToHistory(
            'Оборудование добавлено.',
            'Модель: ' . $gear->title . '. Производитель: ' . $gear->producer
        );
    }

    public function removeGear($gear) {
        $this->record->unlink('gears', $gear, true);
        $this->addToHistory(
            'Оборудование удалено.',
            'Модель: ' . $gear->title . '. Производитель: ' . $gear->producer
        );
    }

}
