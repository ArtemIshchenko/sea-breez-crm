<?php

namespace app\modules\user\models\searches;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\components\interfaces\Search;
use app\modules\user\models\User;
use app\components\data\ActiveDataFilter;

class CustomerSearch extends Model implements Search
{
    public $id;
    public $last_name;
    public $email;
    public $company;
    public $contact_guid;
    public $manager_id;

    public function rules()
    {
        return [
            [['id', 'manager_id'], 'integer'],
            [['email', 'last_name', 'company'], 'trim'],
            [['email', 'last_name', 'company', 'contact_guid'], 'string']
        ];
    }

    public function search(array $params = null, array $filter = null): ActiveDataProvider {
        $query = User::find();
        $query->where(['role' => User::ROLE_CUSTOMER]);
        if (Yii::$app->user->isManager()) {
            // For anager query only own customers
            $query->andWhere(['manager_id' => Yii::$app->user->id]);
        }
        $query->joinWith('projects');
        if (!empty($filter)) {
            $query->andWhere($filter);
        }

        // aggregate data for `ORDER BY count()` clause
        $query->select(['{{%user}}.id', 'email', 'first_name', 'last_name', 'company','contact_guid' ,'{{%user}}.created_at', '{{%user}}.entered_at', 'role', '{{%user}}.status', 'count({{%project}}.id)']);
        $query->groupBy('{{%user}}.id');
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
                    'company',
                    'created_at',
                    'entered_at',
                    'name' => [
                        'asc' => ['last_name' => SORT_ASC],
                        'desc' => ['last_name' => SORT_DESC]
                    ],
                    'one_c_status' => [
                        'asc' => ['contact_guid' => SORT_ASC],
                        'desc' => ['contact_guid' => SORT_DESC]
                    ],
                    'projects_number' => [
                        'asc' => ['count({{%project}}.id)' => SORT_ASC],
                        'desc' => ['count({{%project}}.id)' => SORT_DESC]
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
