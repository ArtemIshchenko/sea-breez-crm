<?php
namespace app\components\translatable;

use Yii;
use yii\base\UnknownClassException;

/**
 * Class for Translatable Active Record
 */
class ActiveRecord extends \app\components\ActiveRecord {
    /**
     * @var array $_translatedAttributes List of translated attributes
     */
    private $_translatableAttributes = [];

    // should be refactored after admin language change widgets done
    protected $currentLang;


    public static function translationClass() {
        return static::class . 'Translation';
    }

    public static function translationTable() {
        $translationClass = static::translationClass();
        return $translationClass::tableName();
    }

    public static function translatableAttributes() {
        $translationSchema = Yii::$app->db->getTableSchema(static::translationTable());
        return array_diff($translationSchema->columnNames, ['id', 'item_id', 'lang']);
    }

    /**
     * Returns list of translation attributes
     * @return array
     */
    public function getTranslatableAttributes() {
        return $this->_translatableAttributes;
    }

    /**
     * Sets the translation attribute values in a massive way.
     * @param array $attributes Attribute values (attributes => values) to be assigned to the translation model.
     */
    public function setTranslatableAttributes($attributes) {
        foreach($attributes as $attribute => $value) {
            if (array_key_exists($attribute, $this->_translatableAttributes)) {
                $this->_translatableAttributes[$attribute] = $value;
            }
        }
    }

    /**
     * @inheritdoc
     * Also checks translation class existance and sets the translation attributes list.
     */
    public function init()
    {
        parent::init();
        if (!class_exists(static::translationClass()))
            throw new UnknownClassException('Translation model class not found.');

        // set translatable attributes
        $this->_translatableAttributes = array_fill_keys(static::translatableAttributes(), null);

        // set current language
        list($lang) = explode('-', Yii::$app->language);
        $this->currentLang = $lang;
    }

    /**
     * PHP getter magic method.
     *
     * {@inheritdoc}
     * Overridden so that translated attributes can be accessed like native.
     */
    public function __get($name) {
        try {
            return parent::__get($name);
        }
        catch (\Exception $e) {
            if (array_key_exists($name, $this->translatableAttributes)) {
                return $this->translatableAttributes[$name];
            }
            throw $e;
        }
    }

    /**
     * PHP setter magic method.
     *
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->_translatableAttributes)) {
            $this->_translatableAttributes[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    // /**
    //  * Returns related translation queries indexed by language
    //  * @return ActiveQueryInterface[] A set of model translation queries
    //  */
    //  Useless?
    // public function getTranslations() {
    //     return $this->hasMany(static::translationClass(), ['item_id' => 'id'])->indexBy('lang');
    // }

    /**
     * Returns a translation query
     * @param  string|null $lang Language code for translation. If empty the current application language will be taken.
     * @return ActiveQueryInterface Translation query object.
     */
    public function getTranslation($lang = null) {
        return $this->hasOne(static::translationClass(), ['item_id' => 'id'])->where(['lang' => $this->currentLang]);
    }

    /**
     * {@inheritdoc}
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find()
    {
        return Yii::createObject([
            'class' => ActiveQuery::class,
            'translationTable' => static::translationTable(),
            'translatableAttributes' => static::translatableAttributes()
        ], [get_called_class()]);
    }

    /**
     * @inheritdoc
     */
    public function fields () {
        return array_merge($this->attributes(), array_keys($this->translatableAttributes));
    }

    /**
     * @inheritdoc
     */
    protected function insertInternal($attributes = null) {
        $primaryAttributes = $attributes ? array_diff($attributes, array_keys($this->translatableAttributes)) : null;
        $result = parent::insertInternal($primaryAttributes);
        if ($result) {
            $this->insertTranslation();
        }
        return $result;
    }

    /**
     * Creates a translation. Note, it doesn't check if translation already exists.
     * @return void
     */
    protected function insertTranslation() {
        $translation = Yii::createObject(array_merge(['class' => static::translationClass(), 'lang' => $this->currentLang], $this->translatableAttributes));
        $this->link('translation', $translation);
    }

    /**
     * @inheritdoc
     */
    protected function updateInternal($attributes = null) {
        $result = parent::updateInternal($attributes);
        // $translationResult = false;
        if (!$this->translation) {
            $translationResult = $this->insertTranslation();
        } else {
            $this->translation->setAttributes($this->translatableAttributes);
            $translationResult = $this->translation->save();
        }
        return $result !== false || $translationResult !== false ? 1 : false;
    }
}
