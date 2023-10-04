<?php

namespace app\modules\project\models\decorators;

use Yii;
use app\components\Decorator;

class CommentDecorator extends Decorator
{
    /**
     * Returns the list of fields that should be returned by default by record's toArray() when no specific fields are specified.
     * @return [type] [description]
     */
    public function fields() {
        $fields = [
            'author' => function($model) {
                return $model->author ? [
                    'id' => $model->author->id,
                    'first_name' => $model->author->first_name,
                    'last_name' => $model->author->last_name
                ] : null;
            },
            'created_at',
            'body'
        ];
        if (!Yii::$app->user->isCustomer()) {
            $fields[] = 'author_visible';
        }
        return $fields;
    }
}
