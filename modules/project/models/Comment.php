<?php

namespace app\modules\project\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use app\modules\user\models\User;
use app\components\interfaces\Decorated;
use app\modules\project\models\decorators\CommentDecorator;

/**
 * This is the model class for table "{{%project_comment}}".
 *
 * @property int $id
 * @property int $project_id
 * @property int $user_id
 * @property string $body
 * @property int $author_visible
 * @property int $created_at
 *
 * @property Project $project
 */
class Comment extends \yii\db\ActiveRecord implements Decorated
{

    /**
     * Handler object containing AR business logic.
     * @var CommentDecorator
     */
    private $_decorator;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%project_comment}}';
    }

    /**
     * {@inheritdoc}
     * @return CommentDecorator
     */
    public function getDecorator() {
        if (!$this->_decorator) {
            $this->_decorator = new CommentDecorator($this);
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
    public function behaviors()
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
                    static::EVENT_BEFORE_INSERT => ['user_id']
                ]
            ]
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['id' => 'project_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
