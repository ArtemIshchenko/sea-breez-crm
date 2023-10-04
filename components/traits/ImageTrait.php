<?php

namespace app\components\traits;

use yii\base\InvalidConfigException;

trait ImageTrait {

    /**
     * Relative images folder path inside of web accessible directory for all models of current class.
     * @return string
     */
    protected abstract function getClassImageFolder();

    /**
     * Return relative images folder path for current model
     * @throws InvalidConfigException If $classImagesFolder is not set.
     * @return string
     */
    public function getImageFolder() {
        return $this->getClassImageFolder() . DIRECTORY_SEPARATOR . $this->id;
    }

    /**
     * Return relative main images folder path for current model
     * @return string
     */
    public function getMainImageFolder() {
        return $this->imageFolder . DIRECTORY_SEPARATOR . 'main';
    }
}
