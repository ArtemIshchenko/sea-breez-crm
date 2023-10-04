<?php

namespace app\modules\gear\controllers\api\admin;

use Yii;
use app\modules\gear\models\Gear;
use app\modules\gear\models\forms\GearForm;
use app\modules\gear\models\searches\GearSearch;

class DefaultController extends \app\modules\gear\controllers\api\BaseDefaultController
{

    /**
     * @inheritdoc
     */
    public function actions() {
        $actions = parent::actions();

        // Index action
        $actions['index']['searchModel'] = GearSearch::class;

        // Update action
        $actions['create']['formModel'] = GearForm::class;

        // Update action
        $actions['update']['formModel'] = GearForm::class;

        return $actions;
    }

}
