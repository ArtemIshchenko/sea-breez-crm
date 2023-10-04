<?php

namespace app\modules\gear\models\searches;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\components\data\ActiveDataFilter;
use app\components\interfaces\Search;
use app\modules\gear\models\Gear;

class GearSearch extends Model implements Search
{
    public $id;
    public $title;
    public $producer;
    public $created_at;
    public $updated_at;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'producer'], 'string'],
            [['title', 'producer'], 'trim'],
            [['id', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * Performs search
     * @param array $params Search params
     * @param array $filter Filter data
     * @return ActiveDataProvider
     */
    public function search(array $params = null, array $filter = null): ActiveDataProvider {
        $query = Gear::find()->joinWith('projects');
        if (!empty($filter)) {
            $query->andWhere($filter);
        }

        return Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $query,
            'pagination' => [
                'params' => $params,
            ],
            'sort' => [
                'params' => $params,
                'defaultOrder' => [
                    'id' => SORT_DESC
                ],
                'attributes' => [
                    'id',
                    'title',
                    'producer',
                    'created_at',
                    'updated_at'            ]
            ]
        ]);
    }

    public function getDataFilter() {
        $attributeMap = [
            'id' => '{{%gear}}.id',
            'title' => '{{%gear}}.title',
            'created_at' => '{{%gear}}.created_at',
            'updated_at' => '{{%gear}}.updated_at'
        ];
        return [
            'class' => ActiveDataFilter::class,
            'searchModel' => $this,
            'attributeMap' => $attributeMap
        ];
    }

}
