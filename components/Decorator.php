<?php

namespace app\components;

use yii\db\ActiveRecord;

/**
 * Active record decorator class.
 *
 * Decorator contains the roperties and methods used to prepare record for use of an [[ActiveRecord]] model in external response.
 *
 * @property string $record
 *
 */
class Decorator {

    /**
     * The corresponding [[ActiveRecord]] model.
     * @var ActiveRecord
     */
    protected $record;

    /**
     * Object constructor
     * @param ActiveRecord $record
     * @return void
     */
    public function __construct(ActiveRecord $record)
    {
        $this->record = $record;
    }
}
