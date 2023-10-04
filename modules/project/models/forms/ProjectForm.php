<?php

namespace app\modules\project\models\forms;

use Yii;
use yii\base\Model;
use yii\helpers\HtmlPurifier;
use yii\web\UnprocessableEntityHttpException;
use app\components\interfaces\Form;
use app\modules\project\models\Project;

class ProjectForm extends Model implements Form
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_SEND = 'send';

    public $title;
    public $coordinates;
    public $address;
    public $date;
    public $client;
    public $auction_link;
    public $delivery_conditions;
    public $subcontractor;
    public $revision_description;
    public $development_prospects;

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
    public function init() {
        parent::init();
        if (!$this->record) {
            $this->record = new Project();
        }
        $this->setAttributes($this->record->attributes, false);
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return [
            'create' => ['title'],
            'update' => [
                'title', 'coordinates', 'address', 'date', 'client', 'auction_link',
                'delivery_conditions', 'subcontractor', 'revision_description',
                'development_prospects'
            ],
            'send' => [
                '!title', '!coordinates', '!address', '!date', '!client', '!auction_link',
                '!delivery_conditions', '!subcontractor', '!revision_description',
                '!development_prospects'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'coordinates', 'address', 'client', 'auction_link', 'subcontractor'], 'trim'],
            [['title', 'coordinates', 'address', 'client', 'auction_link', 'subcontractor'], 'string', 'max' => 255],
            [['auction_link'], 'url'],
            [['delivery_conditions', 'revision_description'], 'string'],
            [[
                'title', 'coordinates', 'address', 'client', 'auction_link',
                'delivery_conditions', 'subcontractor', 'revision_description',
                'development_prospects'], 'filter', 'filter' => [HtmlPurifier::class, 'process']],
            [['title', 'coordinates', 'client', 'date'], 'required', 'on' => self::SCENARIO_SEND]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'title' => Yii::t('project', 'Title'),
            'coordinates' => Yii::t('project', 'Coordinates'),
            'address' => Yii::t('project', 'Address'),
            'date' => Yii::t('project', 'Date'),
            'client' => Yii::t('project', 'Client'),
            'auction_link' => Yii::t('project', 'Auction Link'),
            'delivery_conditions' => Yii::t('project', 'Delivery Conditions'),
            'subcontractor' => Yii::t('project', 'Subcontractor'),
            'revision_description' => Yii::t('project', 'Revision Description'),
            'development_prospects' => Yii::t('project', 'Development prospects')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function save(): bool {
        if (in_array($this->record->status, [Project::STATUS_FINISHED, Project::STATUS_CANCELED, Project::STATUS_REJECTED])) {
            throw new UnprocessableEntityHttpException(Yii::t('project', 'Not possible to update this project. You can create a new one.'));
        }
        if (!$this->validate()) {
            return false;
        }

        $this->record->setAttributes($this->attributes, false);
        // form list of changed old values to save in journal
        $changedAttributes = [];
        foreach ($this->record->dirtyAttributes as $attr => $value) {
            $changedAttributes[] = $this->getAttributeLabel($attr) . ': ' . $this->record->getOldAttribute($attr);
        }
        // save record
        $result = $this->scenario == self::SCENARIO_CREATE
            ? $this->record->handler->create()
            : $this->record->handler->update();

        // save to journal
        if ($result && $this->record->status != Project::STATUS_CREATED && $changedAttributes) {
            $this->record->handler->addToHistory('Редактирование проекта', "Отредактированные данные. \r\n" . implode(" \r\n", $changedAttributes));
        }

        return $result;
    }
}
