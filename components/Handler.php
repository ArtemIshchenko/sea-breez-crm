<?php

namespace app\components;

use yii\db\ActiveRecord;

/**
 * Active record handler class.
 *
 * Handler contains the business logic of an [[ActiveRecord]] model.
 *
 * @property string $record
 *
 */
class Handler {

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
