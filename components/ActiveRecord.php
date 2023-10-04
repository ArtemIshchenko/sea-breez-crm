<?php

namespace app\components;

/**
 * ActiveRecord adds the `status` functionality to standard \yii\db\ActiveRecord class.
 * Define statuses of current AR in statuses() method.
 */
class ActiveRecord extends \yii\db\ActiveRecord {

    const STATUS_NOT_PUBLISHED = 1;
    const STATUS_PUBLISHED = 2;
    const STATUS_SUSPENDED = 3;
    const STATUS_OFFERED = 4;

    const EVENT_BEFORE_PUBLISH = 'beforePublish';
    const EVENT_BEFORE_SUSPEND = 'beforeSuspend';

    /**
     * Array of status labels for api use and status ids as keys.
     * If you want to set or rewrite statuses of AR do it here.
     * Default implementation defines no statuses at all.
     * @return array
     */
    public static function statuses() {
        return [];
    }

    /**
     * Find status label by it's id and return it.
     * @param int Status id
     * @return string|null Status label
     */
    public static function statusLabel($status) {
        return isset(static::statuses()[$status]) ? static::statuses()[$status] : null;
    }

    /**
     * Find status id by it's label and return it.
     * @param string Status label
     * @return int|null Status id
     */
    public static function statusByLabel($label) {
        foreach (static::statuses() as $status => $statusLabel) {
            if ($statusLabel === $label)
                return $status;
        }
        return null;
    }

    /**
     * Change status of model.
     * @param  int|string $status New status as id or label.
     * @return bool If new status was saved.
     */
    public function updateStatus($status) {
        if (!array_key_exists($status, static::statuses()))
            $status = static::statusByLabel($status);
        if (!$status)
            return false;

        $this->status = $status;
        // BEFORE_PUBLISH event is triggered only once
        if ($status == static::STATUS_PUBLISHED && ($this->hasAttribute('published_at') && !$this->published_at))
            $this->trigger(static::EVENT_BEFORE_PUBLISH);
        if ($status == static::STATUS_SUSPENDED)
            $this->trigger(static::EVENT_BEFORE_SUSPEND);

        return $this->save();
    }

}
