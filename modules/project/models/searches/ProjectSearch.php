<?php

namespace app\modules\project\models\searches;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\components\data\ActiveDataFilter;
use app\components\interfaces\Search;
use app\modules\project\models\Project;

class ProjectSearch extends Model implements Search
{
    public $id;
    public $title;
    public $created_at;
    public $updated_at;
    public $date;
    public $status;
    public $client;
    public $company;
    public $designing_deadline;
    public $gear;
    public $gear_producer;
    public $provider;

    public $author;
    public $manager;
    public $designer;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['author', 'manager', 'designer'], 'hasAccess'],
            [['author', 'manager', 'designer', 'title', 'status', 'client', 'company', 'gear_producer', 'provider'], 'string'],
            [['author', 'manager', 'designer', 'title', 'status', 'client', 'company'], 'trim'],
            [['id', 'created_at', 'updated_at', 'date', 'designing_deadline', 'gear'], 'integer'],
            ['status', 'filter', 'filter' => [$this, 'processStatusLabels']]
        ];
    }

    /**
     * Checkes if user has access to search restricted parameters. If no sets parameter to null.
     * @param self $model Search model
     * @param string $attribute Attribute name
     * @return void
     */
    public function hasAccess($attribute) {
        if ($this->{$attribute}) {
            if ($attribute == 'author' && Yii::$app->user->isCustomer()) {
                $this->{$attribute} = null;
            }
            else if ($attribute == 'manager' && !Yii::$app->user->isAdmin() && !Yii::$app->user->isDesigner()) {
                $this->{$attribute} = null;
            }
            else if ($attribute == 'designer' && !Yii::$app->user->isAdmin() && !Yii::$app->user->isManager()) {
                $this->{$attribute} = null;
            }
        }
    }

    /**
     * Process status label to value.
     * @param string $label
     * @return int|null
     */
    public function processStatusLabels($label) {
        $status = array_search($label, Project::statuses());
        return !$status ? null : $status;
    }

    /**
     * Performs search
     * @param array $params Search params
     * @param array $filter Filter data
     * @return ActiveDataProvider
     */
    public function search(array $params = null, array $filter = null): ActiveDataProvider {
        $query = Project::find();
        $joinWith = [];
        $this->_setAccessToQuery($query, $joinWith);

        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        if ($joinWith) {
            $query->joinWith($joinWith);
        }

        if ($this->gear || $this->gear_producer) {
            $query->joinWith('gears', false);
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
                    'id',
                    'title',
                    'date',
                    'created_at',
                    'updated_at',
                    'status',
                    'client',
                    'author' => [
                        'asc' => ['author.last_name' => SORT_ASC],
                        'desc' => ['author.last_name' => SORT_DESC]
                    ],
                    'manager' => [
                        'asc' => ['manager.last_name' => SORT_ASC],
                        'desc' => ['manager.last_name' => SORT_DESC]
                    ],
                    'designer' => [
                        'asc' => ['designer.last_name' => SORT_ASC],
                        'desc' => ['designer.last_name' => SORT_DESC]
                    ]
                ]
            ]
        ]);
    }

    /**
     * Sets query params depending on current user role.
     * @param yii\db\ActiveQueryInterface $query Search query
     * @param array $joinWith Array of relations to join with.
     */
    private function _setAccessToQuery(&$query, &$joinWith) {
        if (Yii::$app->user->isCustomer()) {
            $query->andWhere(['user_id' => Yii::$app->user->id]);
        }
        else {
            $joinWith[] = 'author';
            if (Yii::$app->user->isManager()) {
                // For manager query only own customers
                $query->andWhere(['author.manager_id' => Yii::$app->user->id]);
                $joinWith[] = 'designer';
            }
            else if (Yii::$app->user->isDesigner()) {
                $query->andWhere(['designer_id' => Yii::$app->user->id]);
                $joinWith[] = 'author.manager';
            }
            else {
                $joinWith[] = 'author.manager';
                $joinWith[] = 'designer';
            }
        }
    }

    public function getDataFilter() {
        $attributeMap = [
            'id' => '{{%project}}.id',
            'author' => 'author.id',
            'manager' => 'manager.id',
            'designer' => 'designer.id',
            'status' => '{{%project}}.status',
            'created_at' => '{{%project}}.created_at',
            'updated_at' => '{{%project}}.updated_at',
            'company' => 'author.company',
            'gear' => '{{%gear}}.id',
            'gear_producer' => '{{%gear}}.producer',
            'provider' => 'author.provider',
        ];
        if (Yii::$app->user->isDesigner()) {
            $attributeMap['author'] = 'author.last_name';
            $attributeMap['manager'] = 'manager.last_name';
        }
        return [
            'class' => ActiveDataFilter::class,
            'searchModel' => $this,
            'attributeMap' => $attributeMap
        ];
    }

}
