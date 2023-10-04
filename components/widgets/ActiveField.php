<?php
namespace app\components\widgets;

use Yii;
use yii\helpers\Html;

class ActiveField extends \yii\widgets\ActiveField {

    public $hintOptions = ['class' => 'form-text'];
    public $errorOptions = ['class' => 'invalid-feedback'];
    public $labelOptions = ['class' => 'form-control-label'];

    // public $template = "{label}\n{input}\n{hint}\n{error}";

    public $prepend = null;

    public $append = null;

    /**
     * {@inheritdoc}
     */
//     public function init()
//     {
//         parent::init();
// // var_dump(substr($this->template, strpos($this->template, '{input}') + 7));exit;
//         if ($this->prepend || $this->append) {
//
//
//             // $template = substr($this->template, 0, strpos($this->template, '{input}'));
//             // $template .= '<div class="input-group">';
//             // if ($this->prepend) {
//             //     $template .= (string) $this->prepend;
//             // }
//             // $template .= '{input}';
//             // if ($this->append) {
//             //     $template .= (string) $this->append;
//             // }
//             // $template .= substr($this->template, strpos($this->template, '{input}') + 7);
//
//
//             // $template = "<div class=\"input-group\">" . $this->template . "</div>";
//             // if ($this->prepend) {
//             //     $template = substr_replace($template, '<div class="input-group-prepend">' . $this->prepend . '</div>', strpos($template, '{input}'), 0);
//             // }
//             // if ($this->append) {
//             //     $template = substr_replace($template, '<div class="input-group-append">' . $this->append . '</div>', strpos($template, '{input}') + 7, 0);
//             // }
//             // var_dump($template);exit;
//             $this->template = Html::encode($template);
//         }
//     }

    /**
    * {inheritdoc}
    */
    public function textInput($options = [])
    {
        $options = array_merge($this->inputOptions, $options);
        if ($this->form->validationStateOn === ActiveForm::VALIDATION_STATE_ON_INPUT) {
            $this->addErrorClassIfNeeded($options);
        }
        $this->addAriaAttributes($options);
        $this->adjustLabelFor($options);
        if ($this->prepend === null && $this->append === null) {
            $input = Html::activeTextInput($this->model, $this->attribute, $options);
        }
        else {
            $input = Html::beginTag('div', ['class' => 'input-group']);
            if ($this->prepend) {
                $input .= $this->inputGroupElement(true);
            }
            $input .= Html::activeTextInput($this->model, $this->attribute, $options);
            if ($this->append) {
                $input .= $this->inputGroupElement(false);
            }
            $input .= Html::endTag('div');
        }
        $this->parts['{input}'] = $input;
        return $this;
    }

    /**
    * {inheritdoc}
    */
    public function passwordInput($options = [])
    {
        $options = array_merge($this->inputOptions, $options);
        if ($this->form->validationStateOn === ActiveForm::VALIDATION_STATE_ON_INPUT) {
            $this->addErrorClassIfNeeded($options);
        }
        $this->addAriaAttributes($options);
        $this->adjustLabelFor($options);
        if ($this->prepend === null && $this->append === null) {
            $input = Html::activePasswordInput($this->model, $this->attribute, $options);
        }
        else {
            $input = Html::beginTag('div', ['class' => 'input-group']);
            if ($this->prepend) {
                $input .= $this->inputGroupElement(true);
            }
            $input .= Html::activePasswordInput($this->model, $this->attribute, $options);
            if ($this->append) {
                $input .= $this->inputGroupElement(false);
            }
            $input .= Html::endTag('div');
        }
        $this->parts['{input}'] = $input;
        return $this;
    }

     /**
      * Creates a block to pin on input-group
      * @param  Boolean $prepend If element should be pretended or appended
      * @return String Formed part of input group
      */
     protected function inputGroupElement(bool $prepend) {
         $type = $prepend ? 'prepend' : 'append';
         $input = Html::beginTag('div', ['class' => "input-group-$type"]);
         $input .= $this->{$type};
         $input .= Html::endTag('div');
         return $input;
     }

}
