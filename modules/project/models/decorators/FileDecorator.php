<?php
namespace app\modules\project\models\decorators;

use Yii;
use app\components\Decorator;

class FileDecorator extends Decorator
{

    /**
     * Returns the list of fields that should be returned by default by record's toArray() when no specific fields are specified.
     * @return [type] [description]
     */
    public function fields() {
        $fields = [
            'id',
            'project_id',
            'filename',
            'status',
            'version'
        ];

        return $fields;
    }
    
}
