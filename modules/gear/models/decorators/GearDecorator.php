<?php

namespace app\modules\gear\models\decorators;

use Yii;
use app\components\Decorator;

class GearDecorator extends Decorator
{
    /**
     * Returns the list of fields that should be returned by default by record's toArray() when no specific fields are specified.
     * @return array
     */
    public function fields() {
        $fields = [
            'id',
            'title',
            'producer',
            'description'
        ];
        if (Yii::$app->user->isAdmin()) {
            $fields['projectsNumber'] = function($model) {
                return count($model->projects);
            };
            array_push($fields, 'created_at', 'updated_at');
        }
        return $fields;
    }
}
