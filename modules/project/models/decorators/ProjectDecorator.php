<?php

namespace app\modules\project\models\decorators;

use Yii;
use app\components\Decorator;
use app\modules\project\models\{Project, File};
use yii\db\Query;

class ProjectDecorator extends Decorator
{
    /**
     * Returns the list of fields that should be returned by default by record's toArray() when no specific fields are specified.
     * @return [type] [description]
     */
    public function fields() {
        $fields = [
            'id',
            'title',
            'date',
            'delivery_conditions',
            'revision_description',
            'address',
            'coordinates',
            'client',
            'auction_link',
            'subcontractor',
            'development_prospects',
            'status' => function($model) {
                return Project::statuses()[$model->status];
            },
            'manager_assigned' => function($model) {
                return (bool) $model->author->manager;
            },
            'status_message',
            'created_at',
            'updated_at',
            'designer',
            'technical_task' => function($model) {
                return $model->decorator->getTechnicalTask();
            },
            'specifications' => function($model) {
                return $model->decorator->getSpecification();
            },
            'other_files' => function($model) {
                return $model->decorator->getOtherFiles();
            }
        ];
        if (!Yii::$app->user->isCustomer()) {
            $fields['author'] = function($model) {
                return $model->author ? [
                    'id' => $model->author->id,
                    'first_name' => $model->author->first_name,
                    'last_name' => $model->author->last_name,
                    'middle_name' => $model->author->middle_name,
                    'company' => $model->author->company,
                    'manager' => $model->author->manager ? [
                        'id' => $model->author->manager->id,
                        'first_name' => $model->author->manager->first_name,
                        'middle_name' => $model->author->manager->middle_name,
                        'last_name' => $model->author->manager->last_name
                    ] : null
                ] : null;
            };
            $fields['designer'] = function($model) {
                return $model->designer ? [
                    'id' => $model->designer->id,
                    'first_name' => $model->designer->first_name,
                    'middle_name' => $model->designer->middle_name,
                    'last_name' => $model->designer->last_name,
                    'middle_name' => $model->designer->middle_name
                ] : null;
            };
            $fields[] = 'comments';
            if ($this->record->status == Project::STATUS_DESIGNING) {
                $fields[] = 'designing_deadline';
            }
        } else {
            $fields['comments'] = 'authorVisibleComments';
        }
        return $fields;
    }

    /**
     * Returns technical task
     * @return string|null
     */
    public function getTechnicalTask() {
        $tt = null;
        foreach ($this->record->files as $file) {
            if (
                $file->type == File::TYPE_TECHNICAL_TASK &&
                (!$tt || $tt->version < $file->version) &&
                !$file->isDeleted()
            )
                $tt = $file;
        }
        return $tt;
    }

    /**
     * Returns specification
     * @return array
     */
    public function getSpecification() {
        $specifications = [];
        $files = $this->record
                ->getFiles()
                ->andWhere(['type' => File::TYPE_SPECIFICATION])
                ->andWhere(['deleted_at' => null])
                ->andWhere([
                    'or',
                    [
                        'or',
                        [
                            'or',
                            [
                                'and',
                                ['status' => File::STATUS_SPECIFICATION_GRANTED],
                                [
                                    '=',
                                    'version',
                                    (new Query())
                                        ->from(['pf1' => 'project_file'])
                                        ->select('MAX(`pf1`.`version`)')
                                        ->where(['pf1.project_id' => $this->record->id])
                                        ->andWhere(['pf1.type' => File::TYPE_SPECIFICATION])
                                        ->andWhere(['pf1.imported' => File::NOT_IMPORTED])
                                        ->andWhere(['pf1.deleted_at' => null])
                                        ->andWhere(['pf1.status' => File::STATUS_SPECIFICATION_GRANTED])
                                ]
                            ],
                            ['status' => 0]
                        ],
                        [
                            'and',
                            [
                                'status' => File::STATUS_SPECIFICATION_RETURNED
                            ],
                            [
                                '>=',
                                'version',
                                (new Query())
                                    ->from(['pf2' => 'project_file'])
                                    ->select('MAX(`pf2`.`version`)')
                                    ->where(['pf2.project_id' => $this->record->id])
                                    ->andWhere(['pf2.type' => File::TYPE_SPECIFICATION])
                                    ->andWhere(['pf2.imported' => File::NOT_IMPORTED])
                                    ->andWhere(['pf2.deleted_at' => null])
                            ]
                        ]
                    ],
                    ['status' => File::STATUS_SPECIFICATION_ACCEPTED],
                ])
                ->all();
        foreach ($files as $file) {
            $specifications[] = $file;
        }
        return $specifications;
    }

    /**
     * Returns other files (not technical task and not specification)
     * @return array
     */
    public function getOtherFiles() {
        $files = [];
        foreach ($this->record->files as $file) {
            if (
                $file->type != File::TYPE_TECHNICAL_TASK &&
                $file->type != File::TYPE_SPECIFICATION &&
                !$file->isDeleted()
            )
                $files[] = $file;
        }
        return $files;
    }
}
