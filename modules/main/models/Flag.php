<?php

namespace app\modules\main\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%flag}}".
 *
 * @property int $id
 * @property string $identificator
 * @property int $value
 * @property int $created_at
 */
class Flag extends \yii\db\ActiveRecord
{
    const TYPE_DEADLINE_NOTIFIED = 'project_deadline_notified';
    const TYPE_PROJECT_SENDING_DELAY = 'project_sending_delay';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%flag}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['value'], 'integer'],
            [['identificator'], 'string', 'max' => 255],
            [['identificator'], 'required']
        ];
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
                    static::EVENT_BEFORE_UPDATE => false
                ]
            ]
        ];
    }

    /**
     * Removes old flags
     * @param int $olderThen Timestamp to remove older flags
     * @return int number of removed flags
     */
    public static function clean(string $type, int $olderThen) {
        return static::deleteAll(['and', ['type' => $type], ['<', 'created_at', $olderThen]]);
    }

    /**
     * Checks if flag exists
     * @param  string $identificator [description]
     * @return bool
     */
    public static function exists(string $type, $identificator) {
        return static::find()->where([
            'type' => $type,
            'identificator' => (string) $identificator
        ])->exists();
    }

    /**
     * Adds flag
     * @param string $identificator
     * @param int|null $value
     * @return bool
     */
    public static function add(string $type, $identificator, int $value = null) {
        $flag = new static([
            'type' => $type,
            'identificator' => (string) $identificator,
            'value' => $value
        ]);
        return $flag->save();
    }
}
