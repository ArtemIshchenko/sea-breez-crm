<?php

namespace app\modules\user\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "{{%user_comment}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $author_id
 * @property string $body
 * @property int $created_at
 *
 * @property User $author
 * @property User $user
 */
class Comment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_comment}}';
    }

    // /**
    //  * {@inheritdoc}
    //  */
    // public function scenarios()
    // {
    //     return [
    //         self::SCENARIO_DEFAULT => ['body']
    //     ];
    // }

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
            ],
            [
                'class' => BlameableBehavior::class,
                'attributes' => [
                    static::EVENT_BEFORE_INSERT => ['author_id'],
                    static::EVENT_BEFORE_UPDATE => false
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fields() {
        return [
            'author',
            'body',
            'created_at'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
