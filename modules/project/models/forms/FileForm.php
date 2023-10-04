<?php

namespace app\modules\project\models\forms;

use Yii;
use yii\base\Model;
use app\components\interfaces\Form;
use app\modules\project\models\File;
use app\modules\project\models\handlers\ProjectHandler;

class FileForm extends Model implements Form
{
    public $files;
    public $type;
    public $guid;
    public $status = 0;
    public $imported = 0;
    public $specificationImport = false;

    /**
     * Project model associated with current form
     * @var Project
     */
    private $_record;

    /**
     * Get project model
     * @return Project
     */
    public function getRecord() {
        return $this->_record;
    }

    /**
     * Set project model
     * @param Project $record
     */
    public function setRecord($record) {
        $this->_record = $record;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                ['type'], 'in',
                'range' => $this->_getAlowedTypes()
            ],
            [
                ['files'], 'file',
                'skipOnEmpty' => false,
                'maxFiles' => Yii::$app->params['maxFilesUpload'],
                'maxSize' => Yii::$app->params['maxFilesUploadSize'],
            ]
        ];
    }

    /**
     * Returns allowed file types depending on user role.
     * @return void
     */
    private function _getAlowedTypes() {
        $types = [File::TYPE_GENERAL, File::TYPE_TECHNICAL_TASK, File::TYPE_SPECIFICATION];
        if (Yii::$app->user->isCustomer() && !$this->specificationImport) {
            unset($types[2]);
        }
        return $types;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'files' => Yii::t('project', 'Files'),
            'type' => Yii::t('project', 'Type')
        ];
    }

    /**
     * Saves files
     * @return bool If files saved
     */
    public function save(): bool {
        if (!$this->validate()) {
            return false;
        }
        return $this->record->handler->saveFiles($this->files, $this->type, $this->guid, $this->status, $this->imported);
    }
}
