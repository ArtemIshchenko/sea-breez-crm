<?php
namespace app\components\behaviors;

use yii\behaviors\SluggableBehavior;
use yii\helpers\ArrayHelper;

/**
 * Sluggable behavior with cyrillic to latin transliteration
 */
class CyrillicSlugBehavior extends SluggableBehavior {

    /**
     * Transliteration characters matrix
     */
    public $transliterationMatrix = [
        'й' => 'i',    'ц' => 'c',  'у' => 'u',  'к' => 'k',    'е' => 'e',
        'н' => 'n',    'г' => 'g',  'ш' => 'sh', 'щ' => 'shch', 'з' => 'z',
        'х' => 'h',    'ъ' => '',   'ф' => 'f',  'ы' => 'y',    'в' => 'v',
        'а' => 'a',    'п' => 'p',  'р' => 'r',  'о' => 'o',    'л' => 'l',
        'д' => 'd',    'ж' => 'zh', 'э' => 'e',  'ё' => 'e',    'я' => 'ya',
        'ч' => 'ch',   'с' => 's',  'м' => 'm',  'и' => 'i',    'т' => 't',
        'ь' => '',     'б' => 'b',  'ю' => 'yu', 'ү' => 'u',    'қ' => 'k',
        'ғ' => 'g',    'ә' => 'e',  'ң' => 'n',  'ұ' => 'u',    'ө' => 'o',
        'Һ' => 'h',    'һ' => 'h',  'і' => 'i',  'ї' => 'i',   'є' => 'je',
        'ґ' => 'g',    'Й' => 'I',  'Ц' => 'C',  'У' => 'U',    'Ұ' => 'U',
        'Ө' => 'O',    'К' => 'K',  'Е' => 'E',  'Н' => 'N',    'Г' => 'G',
        'Ш' => 'SH',   'Ә' => 'E',  'Ң '=> 'N',  'З' => 'Z',    'Х' => 'H',
        'Ъ' => '',     'Ф' => 'F',  'Ы' => 'Y',  'В' => 'V',    'А' => 'A',
        'П' => 'P',    'Р' => 'R',  'О' => 'O',  'Л' => 'L',    'Д' => 'D',
        'Ж' => 'ZH',   'Э' => 'E',  'Ё' => 'E',  'Я' => 'YA',   'Ч' => 'CH',
        'С' => 'S',    'М' => 'M',  'И' => 'I',  'Т' => 'T',    'Ь' => '',
        'Б' => 'B',    'Ю' => 'YU', 'Ү' => 'U',  'Қ' => 'K',    'Ғ' => 'G',
        'Щ' => 'SHCH', 'І' => 'I',  'Ї' => 'I', 'Є' => 'YE',   'Ґ' => 'G',
    ];

    /**
     * Transliterates cyrillic to latin charecters
     */
    protected function transliterate($str) {
        return str_replace(array_keys($this->transliterationMatrix), array_values($this->transliterationMatrix), $str);
    }


    /**
     * @inheritdoc
     */
    protected function getValue($event)
    {
        if (!$this->isNewSlugNeeded()) {
            return $this->owner->{$this->slugAttribute};
        }
        if ($this->attribute !== null) {
            $slugParts = [];
            foreach ((array) $this->attribute as $attribute) {
                $part = $this->transliterate(ArrayHelper::getValue($this->owner, $attribute));
                if ($this->skipOnEmpty && $this->isEmpty($part)) {
                    return $this->owner->{$this->slugAttribute};
                }
                $slugParts[] = $part;
            }
            $slug = $this->generateSlug($slugParts);
        } else {
            $slug = parent::getValue($event);
        }
        return $this->ensureUnique ? $this->makeUnique($slug) : $slug;
    }
}