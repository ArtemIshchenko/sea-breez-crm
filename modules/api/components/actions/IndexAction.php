<?php

namespace app\modules\api\components\actions;

use Yii;
use yii\data\ActiveDataFilter;

class IndexAction extends \yii\rest\IndexAction
{
    /**
     * Searching model
     * @var \app\components\interfaces\Search|callback|string|array
     */
    public $searchModel;

    /**
     * {@inheritdoc}
     */
    public function init() {
        parent::init();
        if ($this->searchModel) {
            if (is_callable($this->searchModel)) {
                $this->searchModel = call_user_func($this->searchModel);
            }
            if (is_string($this->searchModel) || is_array($this->searchModel)) {
                $this->searchModel = Yii::createObject($this->searchModel);
            }
            if (!$this->dataFilter) {
                if ($this->searchModel->canGetProperty('dataFilter')) {
                    $this->dataFilter = $this->searchModel->dataFilter;
                }
                else {
                    $this->dataFilter = [
                        'class' => ActiveDataFilter::class,
                        'searchModel' => $this->searchModel
                    ];
                }
            }
            if (!$this->prepareDataProvider) {
                $this->prepareDataProvider = function(self $action, array $filter = null) {
                    return $this->searchModel->search(Yii::$app->request->get(), $filter);
                };
            }
        }
    }
}
