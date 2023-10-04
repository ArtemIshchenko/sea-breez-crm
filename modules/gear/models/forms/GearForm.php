<?php
namespace app\modules\gear\models\forms;

use Yii;
use yii\base\Model;
use app\components\interfaces\Form;
use app\modules\gear\models\Gear;

/**
 * Gear form
 */
class GearForm extends Model implements Form {

    public $title;
    public $producer;
    public $description;

    /**
     * Gear model associated with current form
     * @var Gear
     */
    private $_record;

    /**
     * Get gear model
     * @return Gear
     */
    public function getRecord() {
        return $this->_record;
    }

    /**
     * Set gear model
     * @param Gear $gear
     */
    public function setRecord($record) {
        $this->_record = $record;
    }


    /**
     * {@inheritdoc}
     */
    public function init() {
        parent::init();
        if (!$this->record) {
            $this->record = new Gear();
        }
        $this->setAttributes($this->record->attributes, false);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'producer', 'description'], 'string'],
            [['title'], 'required']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'title' => Yii::t('gear', 'Title'),
            'producer' => Yii::t('gear', 'Producer'),
            'description' => Yii::t('gear', 'Description')
        ];
    }

    /**
     * Saves new comment
     * @return bool
     */
    public function save(): bool {
        if (!$this->validate()) {
            return false;
        }
        try {
            $this->record->setAttributes($this->attributes, false);
            return $this->record->save();
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
    }

}
