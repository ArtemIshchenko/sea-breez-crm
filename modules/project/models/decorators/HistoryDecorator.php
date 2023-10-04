<?php

namespace app\modules\project\models\decorators;

use DateTime;
use Yii;
use app\components\Decorator;

class HistoryDecorator extends Decorator
{
    /**
     * Returns the list of fields that should be returned by default by record's toArray() when no specific fields are specified.
     * @return [type] [description]
     */
    public function fields() {
        if(array_key_exists('external',Yii::$app->params) && Yii::$app->params['external']){
            return [
                'date'=> function($model) {
                    return date(DateTime::ISO8601,$model->created_at);
                },
                'action' => [$this, 'actionField'],
                'additional' => [$this, 'additionalField'],
                'author' => function($model) {
                    return $model->author ? [
                        'id' => $model->author->id,
                        'first_name' => $model->author->first_name,
                        'last_name' => $model->author->last_name
                    ] : null;
                }
            ];
        }
        $fields = [
            'created_at',
            'action' => [$this, 'actionField'],
            'additional' => [$this, 'additionalField'],
            'meta'
        ];

        if (!Yii::$app->user->isCustomer()) {
            $fields['author'] = function($model) {
                return $model->author ? [
                    'id' => $model->author->id,
                    'first_name' => $model->author->first_name,
                    'last_name' => $model->author->last_name
                ] : null;
            };
        }
        return $fields;
    }

    public function actionField($model) {
        return $this->_field($model->action);
    }

    public function additionalField($model) {
        $additionalParts = explode(" \n", $model->additional);
        if (!empty($additionalParts)) {
            foreach ($additionalParts as &$item) {
                $itemParts = explode(". ", $item);
                foreach ($itemParts as &$it1) {
                    $it1Parts = explode(": ", $it1);
                    foreach ($it1Parts as &$it2) {
                        $it2Parts = explode(", ", $it2);
                        foreach ($it2Parts as &$it3) {
                            $it3 = $this->_field($it3);
                        }
                        unset($it3);
                        $it2 = implode(", ", $it2Parts);
                    }
                    unset($it2);
                    $it1 = implode(": ", $it1Parts);
                }
                unset($it1);
                $item = implode(". ", $itemParts);
            }
            unset($item);
            $model->additional = implode(" \r\n", $additionalParts);
        }
        return Yii::t('project', $model->additional);
    }

    public function _field($text) {
        $text = str_replace(
            [
                'января',
                'февраля',
                'марта',
                'апреля',
                'мая',
                'июня',
                'июля',
                'августа',
                'сентября',
                'октября',
                'ноября',
                'декабря',
                'Спецификация',
                'возвращена на доработку'
            ],
            [
                Yii::t('project', 'січня'),
                Yii::t('project', 'лютого'),
                Yii::t('project', 'березня'),
                Yii::t('project', 'квітня'),
                Yii::t('project', 'травня'),
                Yii::t('project', 'червня'),
                Yii::t('project', 'липня'),
                Yii::t('project', 'серпня'),
                Yii::t('project', 'вересня'),
                Yii::t('project', 'жовтня'),
                Yii::t('project', 'листопада'),
                Yii::t('project', 'грудня'),
                Yii::t('project', 'Спецификация'),
                Yii::t('project', 'возвращена на доработку'),
            ],
            $text
        );

        return Yii::t('project', $text);
    }
}
