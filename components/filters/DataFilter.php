<?php
namespace app\modules\api\components\filters;

use Yii;
use yii\data\ActiveDataFilter;

class DataFilter extends ActiveDataFilter {

    /**
     * Resolves bug of wrong attribute in error messages
     * {@inheritdoc}
     */
    protected function validateAttributeValue($attribute, $value)
    {
        $model = $this->getSearchModel();
        if (!$model->isAttributeSafe($attribute)) {
            $this->addError($this->$attribute, $this->parseErrorMessage('unknownAttribute', ['attribute' => $attribute]));
            return;
        }

        $model->{$attribute} = $value;
        if (!$model->validate([$attribute])) {
            $this->addError($attribute, $model->getFirstError($attribute));
            return;
        }
    }

    /**
     * Resolves bug of wrong attribute filtering if `attributeMap` is set
     * {@inheritdoc}
     */
    protected function filterAttributeValue($attribute, $value)
    {
        $mapKey = array_search($attribute, $this->attributeMap);
        if ($mapKey !== false)
            $attribute = $mapKey;
        return parent::filterAttributeValue($attribute, $value);
    }

}
