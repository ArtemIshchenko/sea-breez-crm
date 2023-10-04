<?php
namespace app\components\widgets;

use Yii;

class ActiveForm extends \yii\widgets\ActiveForm {

    /**
     * @inheritdoc
     */
    public $errorCssClass = 'is-invalid';
    
    /**
     * @inheritdoc
     */
    public $fieldClass = 'app\components\widgets\ActiveField';

    public $options = ['class' => 'active-form'];

}