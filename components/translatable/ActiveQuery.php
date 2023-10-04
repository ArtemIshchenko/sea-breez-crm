<?php
namespace app\components\translatable;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Query for translatable active records.
 */
class ActiveQuery extends \yii\db\ActiveQuery {
    /**
     * @var string Name of primary model translation table
     */
    public $translationTable;

    /**
     * @var string[] List of translatable attributes of primary model
     */
    public $translatableAttributes;

    /**
     * If any query condition corresponds the translatable AR property. If true the translation table will be joined in the query automatically.
     * @var boolean
     */
    private $_joinTranslation = false;

    /**
     * {@inheritdoc}
     * Adds 'translation' relation query.
     */
    public function init()
    {
        parent::init();
        if ($this->with === null) {
            $this->with = [];
        }
        $this->with['translation'] = null;
    }

    /**
     * {@inheritdoc}
     * Automatically populates models with translatable attributes.
     */
    public function populate($models)
    {
        $models = parent::populate($models);
        foreach($models as $model) {
            if (is_object($model) && $model->id && $model->translation) {
                $model->setTranslatableAttributes($model->translation->attributes);
            }
        }
        return $models;
    }

    /**
     * @inheritdoc
     */
    public function prepare($builder) {
        $this->prepareTranslatableConditions();
        return parent::prepare($builder);
    }

    /**
     * Sets correct column names to translatable attributes
     * @return void
     */
    public function prepareTranslatableConditions() {
        $this->_prepareTranslatableCondition($this->where);
        $this->_prepareTranslatableOrderBy();
        if ($this->_joinTranslation)
            $this->joinWith('translation');
    }

    /**
     * Sets ORDER BY translatable attributes to [translation_table].attribute form
     * @return void
     */
    private function _prepareTranslatableOrderBy() {
        if (is_array($this->orderBy)) {
            $preparedOrderBy = [];
            foreach($this->orderBy as $attribute => $order) {
                if ($this->_isTranslatableAttribute($attribute)) {
                    $preparedOrderBy[$this->translationTable . '.' . $attribute] = $order;
                    $this->_joinTranslation = true;
                }
                else {
                    $preparedOrderBy[$attribute] = $order;
                }
            }
            $this->orderBy = $preparedOrderBy;
        }
    }

    /**
     * Sets translatable attributes in query to [translation_table].attribute form
     * @param  array $condition A search condition
     * @return void
     */
    private function _prepareTranslatableCondition(&$condition) {
        if (!is_array($condition)) {
            return;
        }

        if (isset($condition[0])) { // operator format: operator, operand 1, operand 2
            if (strtolower($condition[0]) == 'not') {
                $this->_prepareTranslatableCondition($condition[1]);
            }
            elseif (strtolower($condition[0]) == 'and' || strtolower($condition[0]) == 'or') {
                $i = 1;
                do {
                    $this->_prepareTranslatableCondition($condition[$i]);
                    $i++;
                } while (isset($condition[$i]) && $condition[$i]);
            }
            elseif (isset($condition[1]) && is_array($condition[1]))  {
                $this->_prepareTranslatableCondition($condition[1]);
            }
            elseif (isset($condition[1]) && is_string($condition[1]) && $this->_isTranslatableAttribute($condition[1])) {
                $condition[1] = $this->translationTable . '.' . $condition[1];
                $this->_joinTranslation = true;
            }
        }
        else {
            // hash format: 'column1' => 'value1', 'column2' => 'value2', ...
            foreach($condition as $column => $value) {
                if ($this->_isTranslatableAttribute($column)) {
                    $condition[$this->translationTable . '.' . $column] = $value;
                    unset($condition[$column]);
                    $this->_joinTranslation = true;
                }
            }
        }
    }

    /**
     * If attribute belongs to translation table.
     * @param  string
     * @return boolean
     */
    private function _isTranslatableAttribute($attribute) {
        return in_array($attribute, $this->translatableAttributes);
    }
}
