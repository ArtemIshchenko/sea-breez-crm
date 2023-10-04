<?php

namespace app\components\interfaces;

/**
 * Interface that should be implemented by [[yii\db\ActiveRecord]] classes.
 * It's purpose is to move decorating roperties and methods to external object called Decorator and make the AR more SRP-compatible.
 */
interface Decorated {

    /**
     * Returns decorator.
     * @return \app\components\Decorator
     */
    public function getDecorator();
}
