<?php

namespace app\modules\project\models\handlers;

use Yii;
use app\components\Handler;
use app\modules\project\models\{Project, File, History};

class FileHandler extends Handler
{
    /**
     * Saves upliaded file
     * @param \yii\web\UploadedFile $uploadedInstance
     * @return bool weather file saved in uploads folder
     */
    public function upload($uploadedInstance) {
        return $uploadedInstance->saveAs($this->record->project->fileFolder . '/' . $this->record->id);
    }

    /**
     * Softly removes file
     * @return bool
     */
    public function delete() {
        $this->record->deleted_at = time();
        if ($this->record->save()) {
            $this->record->project->handler->fileDeleted($this->record);
            return true;
        }
        return false;
    }

}
