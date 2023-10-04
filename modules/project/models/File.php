<?php

namespace app\modules\project\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\FileHelper;
use app\components\interfaces\{Handled, Decorated};
use app\modules\project\models\handlers\FileHandler;
use app\modules\project\models\decorators\FileDecorator;

/**
 * This is the model class for table "{{%project_file}}".
 *
 * @property int $id
 * @property int $project_id
 * @property string $type
 * @property string $filename
 * @property int $version
 * @property int $status
 * @property int $imported
 * @property string $specification_data
 * @property int $created_at
 * @property int $deleted_at
 *
 * @property Project $project
 */
class File extends \yii\db\ActiveRecord implements Handled, Decorated
{
    const TYPE_GENERAL = 'general';
    const TYPE_TECHNICAL_TASK = 'technical_task';
    const TYPE_SPECIFICATION = 'specification';

    const STATUS_SPECIFICATION_GRANTED = 1;
    const STATUS_SPECIFICATION_RETURNED = 2;
    const STATUS_SPECIFICATION_ACCEPTED = 3;
    const STATUS_SPECIFICATION_IMPORTED = 4;

    const NOT_IMPORTED = 0;
    const IMPORTED = 1;

    /**
     * Handler object containing AR business logic.
     * @var FileHandler
     */
    private $_handler;

    /**
     * Handler object containing AR decorating logic.
     * @var ProjectDecorator
     */
    private $_decorator;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%project_file}}';
    }

    /**
     * {@inheritdoc}
     * @return FileHandler
     */
    public function getHandler() {
        if (!$this->_handler) {
            $this->_handler = new FileHandler($this);
        }
        return $this->_handler;
    }

    /**
     * {@inheritdoc}
     * @return FileDecorator
     */
    public function getDecorator() {
        if (!$this->_decorator) {
            $this->_decorator = new FileDecorator($this);
        }
        return $this->_decorator;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::class, ['id' => 'project_id'])->inverseOf('files');
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    static::EVENT_BEFORE_INSERT => ['created_at']
                ]
            ]
        ];
    }

    /**
     * Get file path
     * @return string
     */
    public function getPath() {
        return $this->project->fileFolder . '/' . $this->filename;
    }

    /**
     * Checks if file is softly deleted.
     * @return boolean
     */
    public function isDeleted() {
        return $this->deleted_at != null;
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete() {
        parent::afterDelete();
        FileHelper::unlink($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return $this->decorator->fields();
    }
}
