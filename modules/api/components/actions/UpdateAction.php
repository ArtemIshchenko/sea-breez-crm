<?php

namespace app\modules\api\components\actions;

use Yii;
use app\components\helpers\ErrorHelper;

class UpdateAction extends \yii\rest\UpdateAction
{
    /**
     * Class of form model.
     * @var str
     */
    public $formModel;

    /**
     * Updates an existing model.
     * @param string $id the primary key of the model.
     * @return \yii\db\ActiveRecordInterface|\app\components\interfaces\Form the model being updated or form model in case of validation errors
     */
    public function run($id)
    {
        if (!$this->formModel) {
            return parent::run($id);
        }

        $model = $this->findModel($id);
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }
        $form = new $this->formModel([
            'record' => $model
        ]);
        if ($this->scenario) {
            $form->scenario = $this->scenario;
        }
        if (!$form->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            ErrorHelper::throwInputNotLoaded();
        }
        if (!$form->save()) {
            ErrorHelper::checkModelHasErrors($form);
            return $form;
        }
        return $form->record;
    }
}
