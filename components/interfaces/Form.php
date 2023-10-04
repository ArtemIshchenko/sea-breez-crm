<?php

namespace app\components\interfaces;

use yii\db\ActiveRecordInterface;

interface Form {
    /**
     * Saves form data
     * @return bool
     */
    public function save(): bool;

    /**
     * Get record model
     * @return ActiveRecordInterface
     */
    public function getRecord();

    /**
     * Set record model
     * @param ActiveRecordInterface $record
     */
    public function setRecord($record);
}
