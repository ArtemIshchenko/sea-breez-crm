<?php

namespace app\components;

use app\modules\user\models\User as UserIdentity;

class User extends \yii\web\User {

    /**
     * Checks if current user is admin.
     * @return boolean
     */
    public function isAdmin() {
        return $this->identity && $this->identity->role == UserIdentity::ROLE_ADMIN;
    }

    /**
     * Checks if current user is customer.
     * @return boolean
     */
    public function isCustomer() {
        return $this->identity && $this->identity->role == UserIdentity::ROLE_CUSTOMER;
    }

    /**
     * Checks if current user is manager.
     * @return boolean
     */
    public function isManager() {
        return $this->identity && $this->identity->role == UserIdentity::ROLE_MANAGER;
    }

    /**
     * Checks if current user is designer.
     * @return boolean
     */
    public function isDesigner() {
        return $this->identity && $this->identity->role == UserIdentity::ROLE_DESIGNER;
    }

    /**
     * Checks if current user is an active (is already approved and not suspended).
     * @return boolean
     */
    public function isActive() {
        return $this->identity && $this->identity->status == UserIdentity::STATUS_ACTIVE;
    }


    /**
     * Checks if current user is customer.
     * @return boolean
     */
    public function isOneC() {
        return $this->identity && $this->identity->role == UserIdentity::ROLE_1C;
    }

}
