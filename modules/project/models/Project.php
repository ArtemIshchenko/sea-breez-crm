<?php

namespace app\modules\project\models;

use Yii;
use yii\helpers\FileHelper;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use app\components\interfaces\{Handled, Decorated};
use app\modules\project\models\handlers\ProjectHandler;
use app\modules\project\models\decorators\ProjectDecorator;
use app\modules\user\models\User;
use app\modules\gear\models\Gear;

/**
 * This is the model class for table "{{%project}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $designer_id
 * @property string $title
 * @property string $address
 * @property string $coordinates
 * @property int $date
 * @property string $client
 * @property string $auction_link
 * @property string $delivery_conditions
 * @property string $subcontractor
 * @property string $revision_description
 * @property string $development_prospects
 * @property string $status_message
 * @property int $designing_deadline
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $designer
 * @property User $user
 * @property File[] $projectFiles
 */
class Project extends \yii\db\ActiveRecord implements Handled, Decorated
{
    const STATUS_CREATED = 1;
    const STATUS_SENT = 2;
    const STATUS_RETURNED = 3;
    const STATUS_REJECTED = 4;
    const STATUS_DESIGNING = 5;
    const STATUS_SPECIFICATION_GRANTED = 6;
    const STATUS_SPECIFICATION_ACCEPTED = 7;
    const STATUS_CANCELED = 8;
    const STATUS_FINISHED = 9;

    /**
     * Handler object containing AR business logic.
     * @var ProjectHandler
     */
    private $_handler;

    /**
     * Handler object containing AR decorating logic.
     * @var ProjectDecorator
     */
    private $_decorator;

    /**
     * {@inheritdoc}
     * @return ProjectHandler
     */
    public function getHandler() {
        if (!$this->_handler) {
            $this->_handler = new ProjectHandler($this);
        }
        return $this->_handler;
    }

    /**
     * {@inheritdoc}
     * @return ProjectDecorator
     */
    public function getDecorator() {
        if (!$this->_decorator) {
            $this->_decorator = new ProjectDecorator($this);
        }
        return $this->_decorator;
    }

    /**
     * Status labels
     * @return array
     */
    public static function statuses() {
        return [
            self::STATUS_CREATED => 'created',
            self::STATUS_SENT => 'sent',
            self::STATUS_RETURNED => 'returned',
            self::STATUS_REJECTED => 'rejected',
            self::STATUS_DESIGNING => 'designing',
            self::STATUS_SPECIFICATION_GRANTED => 'specification_granted',
            self::STATUS_SPECIFICATION_ACCEPTED => 'specification_accepted',
            self::STATUS_CANCELED => 'canceled',
            self::STATUS_FINISHED => 'finished'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%project}}';
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
            ],
            [
                'class' => BlameableBehavior::class,
                'attributes' => [
                    static::EVENT_BEFORE_INSERT => ['user_id'],
                    static::EVENT_BEFORE_UPDATE => false
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
    public function getDesigner()
    {
        return $this->hasOne(User::class, ['id' => 'designer_id'])
            ->from(['designer' => User::tableName()]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'user_id'])
            ->from(['author' => User::tableName()]);
    }


    /**
     * find project by one_c_guid from 1C
     * {@inheritdoc}
     */
    public static function findProjectByOneCGuid($guid)
    {
        return static::findOne(['one_c_guid' => $guid]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHistory()
    {
        return $this->hasMany(History::class, ['project_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['project_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthorVisibleComments()
    {
        return $this->hasMany(Comment::class, ['project_id' => 'id'])
            ->onCondition(['author_visible' => true]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(File::class, ['project_id' => 'id'])->inverseOf('project');
    }

    /**
     * Creates if not exists and returns file uploads folder
     * @return string
     */
    public function getFileFolder() {
        $folder = Yii::getAlias(Yii::$app->params['restrictedUploadsFolder']
            . '/'
            . Yii::$app->params['project.uploadFolder']
            . '/'
            . $this->id);
        FileHelper::createDirectory($folder);
        return $folder;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGears()
    {
        return $this->hasMany(Gear::class, ['id' => 'gear_id'])
            ->viaTable('project_gear', ['project_id' => 'id']);
    }
}
