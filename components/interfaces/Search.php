<?php

namespace app\components\interfaces;

use yii\data\ActiveDataProvider;

interface Search {
    /**
     * Performs search
     * @param  array|null $params array of params for data provider, typically received from [[yii\base\Request::get]].
     * @param  array|null $filter Array containing filter specification from [[yii\data\DataFilter::build]]
     * @return ActiveProvider
     */
    public function search(array $params = null, array $filter = null): ActiveDataProvider;
}
