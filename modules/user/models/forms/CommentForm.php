<?php
namespace app\modules\user\models\forms;

use Yii;
use yii\base\Model;
use yii\helpers\HtmlPurifier;
use app\components\interfaces\Form;
use app\modules\user\models\{User, Comment};

/**
 * User comment create form
 */
class CommentForm extends Model implements Form {

    public $comment;

    /**
     * User model associated with current form
     * @var User
     */
    private $_record;

    /**
     * Get user model
     * @return User
     */
    public function getRecord() {
        return $this->_record;
    }

    /**
     * Set user model
     * @param User $user
     */
    public function setRecord($record) {
        $this->_record = $record;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['comment'], 'string'],
            [['comment'], 'filter', 'filter' => [HtmlPurifier::class, 'process']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'comment' => Yii::t('user', 'Comment')
        ];
    }

    /**
     * Saves new comment
     * @return bool
     */
    public function save(): bool {
        $user = $this->record;
        if (!$this->validate()) {
            return false;
        }
        $comment = new Comment([
            'body' => $this->comment
        ]);

        try {
            $comment->link('user', $user);
            return true;
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
    }

}
