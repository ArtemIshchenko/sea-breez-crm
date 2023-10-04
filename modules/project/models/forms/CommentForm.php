<?php
namespace app\modules\project\models\forms;

use Yii;
use yii\base\Model;
use yii\helpers\HtmlPurifier;
use app\components\interfaces\Form;
use app\modules\project\models\{Project, Comment};

/**
 * Project comment create form
 */
class CommentForm extends Model implements Form {

    public $comment;
    public $author_visible;

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
     * @param Project $project
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
            [['comment'], 'required'],
            [['comment'], 'filter', 'filter' => [HtmlPurifier::class, 'process']],
            [['author_visible'], 'boolean']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'comment' => Yii::t('project', 'Comment'),
            'author_visible' => Yii::t('project', 'Visible for author')
        ];
    }

    /**
     * Saves new comment
     * @return bool
     */
    public function save(): bool {
        $project = $this->record;
        if (!$this->validate()) {
            return false;
        }
        try {
            $this->record->handler->addComment($this->comment, $this->author_visible);
            return true;
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
    }

}
