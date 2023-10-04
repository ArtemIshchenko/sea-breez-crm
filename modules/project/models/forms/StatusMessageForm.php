<?php

namespace app\modules\project\models\forms;

use Yii;
use yii\base\Model;
use yii\helpers\HtmlPurifier;
use app\components\interfaces\Form;
use app\modules\project\models\File;
use app\modules\project\models\handlers\ProjectHandler;

class StatusMessageForm extends Model implements Form
{
    public $message;

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
                ['message'], 'string'
            ],
            [
                ['message'], 'filter', 'filter' => [HtmlPurifier::class, 'process']
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'message' => Yii::t('project', 'Message')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function save(): bool {
        // this model is for validation only
        return false;
    }
}
