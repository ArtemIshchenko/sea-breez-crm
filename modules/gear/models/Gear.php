<?php

namespace app\modules\gear\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\components\interfaces\{Decorated};
use app\modules\gear\models\decorators\GearDecorator;
use app\modules\project\models\Project;

/**
 * This is the model class for table "{{%gear}}".
 *
 * @property int $id
 * @property string $title
 * @property string $producer
 * @property string $description
 * @property int $created_at
 * @property int $updated_at
 */
class Gear extends \yii\db\ActiveRecord implements Decorated
{
    /**
     * Handler object containing AR decorating logic.
     * @var GearDecorator
     */
    private $_decorator;

    /**
     * {@inheritdoc}
     * @return GearDecorator
     */
    public function getDecorator() {
        if (!$this->_decorator) {
            $this->_decorator = new GearDecorator($this);
        }
        return $this->_decorator;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%gear}}';
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
                    static::EVENT_BEFORE_INSERT => ['created_at'],
                    static::EVENT_BEFORE_UPDATE => ['updated_at']
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return $this->decorator->fields();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjects()
    {
        return $this->hasMany(Project::class, ['id' => 'project_id'])
            ->viaTable('project_gear', ['gear_id' => 'id']);
    }
}
