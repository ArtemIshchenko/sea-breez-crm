<?php

namespace app\modules\user\models\searches;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\components\interfaces\Search;
use app\modules\user\models\User;
use app\components\data\ActiveDataFilter;

class DesignerSearch extends Model implements Search
{
    public $id;
    public $last_name;
    public $email;
    public $company;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['email', 'last_name', 'company'], 'trim'],
            [['email', 'last_name', 'company'], 'string']
        ];
    }

    public function search(array $params = null, array $filter = null): ActiveDataProvider {
        $query = User::find();
        $query->where(['role' => User::ROLE_DESIGNER]);
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
                    'created_at' => SORT_DESC
                ],
                'attributes' => [
                    'email',
                    'created_at',
                    'one_c_status' => [
                        'asc' => ['contact_guid' => SORT_ASC],
                        'desc' => ['contact_guid' => SORT_DESC]
                    ],
                    'entered_at',
                    'name' => [
                        'asc' => ['last_name' => SORT_ASC],
                        'desc' => ['last_name' => SORT_DESC]
                    ],
                    'company',
                    'status'
                ]
            ]
        ]);
    }

    public function getDataFilter() {
        $attributeMap = [
            'id' => '{{%user}}.id'
        ];
        return [
            'class' => ActiveDataFilter::class,
            'searchModel' => $this,
            'attributeMap' => $attributeMap
        ];
    }


}
