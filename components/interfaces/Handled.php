<?php

namespace app\components\interfaces;

/**
 * Interface that should be implemented by [[yii\db\ActiveRecord]] classes.
 * It's purpose is to move business logic to external object called Handler and make the AR more SRP-compatible.
 */
interface Handled {

    /**
     * Returns handler.
     * @return \app\components\Handler
     */
    public function getHandler();
}
