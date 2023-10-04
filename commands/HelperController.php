<?php

namespace app\commands;

use app\modules\project\models\File;
use app\modules\project\models\Project;
use app\modules\user\models\forms\GuidForm;
use app\modules\user\models\User;
use linslin\yii2\curl\Curl;
use Yii;
use yii\console\Controller;

class HelperController extends Controller
{
    public function actionIndex() {
        $dir = './uploads/projects';
//        $count = 0;
//        if (is_dir($dir)) {
//            if ($dh = opendir($dir)) {
//                while (($file = readdir($dh)) !== false) {
//                    if (($file != '.') && ($file != '..')) {
//                        $access = substr(sprintf('%o', fileperms($dir . '/' . $file)), -4);
//                        if (!in_array($access, ['0775', '0777']) && (intval($file) >= 6100)) {
//                            ++$count;
//                            echo "файл: $file" . ' Access: ' . $access . "\n";
//                            //chmod($dir . '/' . $file, 0777);
//                        }
//                    }
//                }
//                closedir($dh);
//            }
//        }


//        $countProjects = Project::find()
//                ->where(['>', 'created_at', strtotime('2019-01-01')])
//                ->orderBy(['created_at' => SORT_DESC])
//                ->asArray()
//                ->count();
//        echo $countProjects;

          $projects = Project::find()
                ->where(['>', 'created_at', strtotime('2019-01-01')])
                ->orderBy(['created_at' => SORT_DESC])
//                ->offset(0)
//                ->limit(2)
                ->asArray()
                ->all();
          if ($projects) {
              foreach ($projects as $project) {
                $files = File::find()
                    ->where([
                        'project_id' => $project['id'],
                        'type' => [File::TYPE_GENERAL, File::TYPE_TECHNICAL_TASK]
                    ])
                    ->all();
                if ($files) {
                    foreach ($files as $file) {
                        if (preg_match('/.*\.(xls)|(xlsx)|(txt)$/i', $file->filename)) {
                            $filePath = $dir . '/' . $project['id'] . '/' . $file->id;
                            $newFilePath = './uploads/all_files/' . $file->filename;
                            if (file_exists($filePath)) {
                                echo $file->filename;
                                echo "\n";
                                if (!@copy($filePath, $newFilePath)) {
                                    echo "не удалось скопировать {$file->filename}...\n";
                                }
                            }
                        }
                    }
                }
              }
          }


//            $ch = curl_init(Yii::$app->params['SPECIFICATION_PARSER']['baseUrl'] . '/upload_file_specification');
//            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//                'Content-Type:multipart/form-data',
//                'Authorization:Bearer ' . Yii::$app->params['SPECIFICATION_PARSER']['bearerToken'],
//                'Client-id:'.  Yii::$app->params['SPECIFICATION_PARSER']['clientId']
//            ));
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//
//            curl_setopt($ch, CURLOPT_POST, true);
//            curl_setopt($ch, CURLOPT_POSTFIELDS, [
//                'file' => $file
//            ]);
//
//            $result = curl_exec($ch);
//            print_r($result);
    }

}