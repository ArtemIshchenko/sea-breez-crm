<?php

namespace app\modules\user\models;

use app\components\interfaces\Decorated;
use app\modules\user\models\decorators\HistoryDecorator;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

class History extends \yii\db\ActiveRecord implements Decorated
{

    /**
     * Handler object containing AR business logic.
     */
    private $_decorator;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_history}}';
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return HistoryDecorator
     */
    public function getDecorator(): HistoryDecorator
    {
        if (!$this->_decorator) {
            $this->_decorator = new HistoryDecorator($this);
        }
        return $this->_decorator;
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return $this->decorator->fields();
    }



    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    static::EVENT_BEFORE_INSERT => ['created_at']
                ]
            ],
            [
                'class' => BlameableBehavior::class,
                'attributes' => [
                    static::EVENT_BEFORE_INSERT => ['author_id'],
                ]
            ]
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }
}