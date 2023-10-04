<?php

namespace app\modules\user\models\decorators;

use Yii;
use app\components\Decorator;

class HistoryDecorator extends Decorator
{
    /**
     * Returns the list of fields that should be returned by default by record's toArray() when no specific fields are specified.
     * @return [type] [description]
     */
    public function fields() {
        $fields = [
            'created_at',
            'action' => function($model) {
                return Yii::t('user', $model->action);
            },
            'additional' => function($model) {
                $additionalParts = explode(" \n", $model->additional);
                if (!empty($additionalParts)) {
                    foreach ($additionalParts as &$item) {
                        $itemParts = explode(":", $item);
                        foreach ($itemParts as &$it) {
                            $it = Yii::t('user', $it);
                        }
                        unset($it);
                        $item = implode(":", $itemParts);
                    }
                    unset($item);
                    $model->additional = implode(" \r\n", $additionalParts);
                }
                return Yii::t('user', $model->additional);
            },
            'type'
        ];

        if (!Yii::$app->user->isCustomer()) {
            $fields['author'] = function($model) {
                return $model->author ? [
                    'id' => $model->author->id,
                    'first_name' => $model->author->first_name,
                    'last_name' => $model->author->last_name
                ] : null;
            };
            $fields['user'] = function($model) {
                return $model->user ? [
                    'id' => $model->user->id,
                    'first_name' => $model->user->first_name,
                    'last_name' => $model->user->last_name
                ] : null;
            };
        }
        return $fields;
    }
}
