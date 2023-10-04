<?php

namespace app\modules\project\controllers\api;

use Yii;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use app\components\helpers\ErrorHelper;
use app\modules\project\models\{Project, File};
use app\modules\project\models\forms\FileForm;

class BaseFileController extends \app\modules\api\components\controllers\Controller
{

    /**
     * Finds project
     * @param int $id
     */
    protected function findProject($id) {
        throw new ServerErrorHttpException('findProject method has to be overwritten.');
    }

    /**
     * Uploads new files
     * @param int $projectId
     * @param string $type
     * @return Project|FileForm|array
     */
    public function actionCreate($projectId, $type) {
        $project = $this->findProject($projectId);
        $status = Yii::$app->request->getQueryParam("status", 0);
        $imported = File::NOT_IMPORTED;
        if ($status == File::STATUS_SPECIFICATION_IMPORTED) {
            File::deleteAll([
                'project_id' => $projectId,
                'type' => File::TYPE_SPECIFICATION,
                'imported' => File::NOT_IMPORTED,
                'status' => File::STATUS_SPECIFICATION_IMPORTED
            ]);
        }
        $form = new FileForm([
            'record' => $project,
            'type' => $type,
            'files' => UploadedFile::getInstancesByName('files'),
            'status' => $status,
            'imported' => $imported,
            'specificationImport' => true,
        ]);
        if (!$form->save()) {
            ErrorHelper::checkModelHasErrors($form);
            return $form;
        }
        if ($status == File::STATUS_SPECIFICATION_IMPORTED) {
            $file = File::find()
                ->where(['project_id' => $projectId])
                ->andWhere(['type' => File::TYPE_SPECIFICATION])
                ->andWhere(['status' => File::STATUS_SPECIFICATION_IMPORTED])
                ->orderBy(['created_at' => SORT_DESC])
                ->one();
            if ($file) {
                return $project->handler->requestToSpecificationParser($project->getFileFolder() . '/' . $file->id, $file->id, $file->filename);
            }
        }
        return $form->record;
    }

    /**
     * Uploads new files
     * @param int $projectId
     * @return array|boolean
     */
    public function actionTune($projectId) {
        $project = $this->findProject($projectId);
        $fileId = Yii::$app->request->getQueryParam("file_id", 0);
        $jobId = Yii::$app->request->getQueryParam("job_id", '');
        $tuneParams = Yii::$app->request->getQueryParam("tune_params", []);
        $file = File::findOne($fileId);

        if ($file) {
            return $project->handler->requestToSpecificationParserTune($fileId, $file->filename, $jobId, $tuneParams);
        }
        return false;
    }

    /**
     * Downloads file
     * @param int $projectId
     * @param string $filename
     * @return \yii\web\Response
     */
    public function actionDownload($projectId, $id) {
        $project = $this->findProject($projectId);
        $file = $project->handler->findFileById($id);
        if (!$file) {
            throw new NotFoundHttpException(Yii::t('project', 'File not found.'));
        }
        $options = [];
        if (preg_match('/.*\.xls$/i', $file->filename)) {
            $options['mimeType'] = 'application/vnd.ms-excel';
        } elseif (preg_match('/.*\.xlsx$/i', $file->filename)) {
            $options['mimeType'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }
        return Yii::$app->response->sendFile($project->getFileFolder() . '/' . $file->id, $file->filename, $options);
    }

    /**
     * Uploads new files
     * @param int $projectId
     * @return Project|FileForm|array
     */
    public function actionImported($projectId) {
        $response = ['result' => 'error'];
        $project = $this->findProject($projectId);
        if ($project) {
            $response['result'] = 'success';
            $response['files'] = [];
            $files = File::find()
                ->where(['project_id' => $projectId])
                ->andWhere(['type' => File::TYPE_SPECIFICATION])
                ->andWhere(['imported' => File::IMPORTED])
                ->andWhere(['deleted_at' => null])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();
            if ($files) {
                foreach ($files as $file) {
                    $response['files'][] = [
                        'filename' => $file->filename,
                        'id' => $file->id,
                        'project_id' =>  $file->project_id,
                        'status' => $file->status,
                    ];
                }
            }
        }

        return $response;
    }

    /**
     * Removes file
     * @param int $projectId
     * @param string $filename
     * @return Project
     */
    public function actionDelete($projectId, $id) {
        $project = $this->findProject($projectId);
        $file = $project->handler->findFileById($id);
        if (!$file) {
            throw new NotFoundHttpException(Yii::t('project', 'File not found.'));
        }
        if ($file->handler->delete() === false) {
            throw new ServerErrorHttpException(Yii::t('project', 'Failed to delete project file.'));
        }

        Yii::$app->getResponse()->setStatusCode(204);
        return $project;
    }


}
