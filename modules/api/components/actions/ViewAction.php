<?php

namespace app\modules\api\components\actions;


use yii\base\Model;

class ViewAction extends \yii\rest\ViewAction {

    /**
     * {@inheritdoc}
     * Automatically set the `View` model scenario if defined.
     */
    public function run($id) {
        $model = parent::run($id);

        $viewScenarioConstant = get_class($model) . '::SCENARIO_VIEW';
        if (defined($viewScenarioConstant)) {
            $model->setScenario(constant($viewScenarioConstant));
        }

        return $model;
    }

}
