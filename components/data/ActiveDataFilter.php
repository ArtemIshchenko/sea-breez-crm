<?php
namespace app\components\data;

use Yii;

class ActiveDataFilter extends \yii\data\ActiveDataFilter {

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
