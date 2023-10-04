<?php

namespace app\modules\project\controllers\api\admin;

use Yii;
use app\modules\project\models\Project;
use app\modules\project\models\forms\{ProjectForm};
use app\modules\project\models\searches\ProjectSearch;

class DefaultController extends \app\modules\project\controllers\api\BaseDefaultController
{

    /**
     * @inheritdoc
     */
    public function actions() {
        $actions = parent::actions();

        // Index action
        $actions['index']['searchModel'] = ProjectSearch::class;

        // Create action
        unset($actions['create']);

        // Update action
        $actions['update']['formModel'] = ProjectForm::class;
        $actions['update']['scenario'] = ProjectForm::SCENARIO_UPDATE;

        // Delete action
        unset($actions['delete']);

        return $actions;
    }

}
