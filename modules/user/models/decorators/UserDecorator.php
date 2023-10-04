<?php

namespace app\modules\user\models\decorators;

use Yii;
use app\components\Decorator;
use app\modules\user\models\{User};
use app\modules\project\models\Project;

class UserDecorator extends Decorator
{
    /**
     * Returns the list of fields that should be returned by default by record's toArray() when no specific fields are specified.
     * @return [type] [description]
     */
    public function fields() {
        if(array_key_exists('external',Yii::$app->params) && Yii::$app->params['external']){
            return [
                'id',
                'role' => function($model) {
                    return User::roles()[$model->role];
                },
                'status' => function($model) {
                    return User::statuses()[$model->status];
                },
                'projects_number' => function($model) {
                    return $model->role == User::ROLE_CUSTOMER ? count($this->getProjects()) : null;
                },
                'active_projects_number' => function($model) {
                    return $model->role == User::ROLE_CUSTOMER ? count($this->getActiveProjects()): null;
                },
                'finished_projects_number' => function($model) {
                    return $model->role == User::ROLE_CUSTOMER ? count($this->getFinishedProjects()): null;
                },
                'email',
                'first_name',
                'middle_name',
                'last_name',
                'company',
                'manager'=> function($model) {
                    return $model->manager ? [
                        'full_name' => $model->manager->first_name.' '.$model->manager->last_name,
                        'id' =>  $model->manager->id,
                        'guid' => $model->manager->contact_guid
                    ] : null;
                },
                'mobile_phone',
                'mobile_phone_verified',
                'phone',
                'register_date'=> function($model) {
                    return date('d.m.Y',$model->created_at);
                },
                'last_update'=> function($model) {
                    return date('d.m.Y',$model->updated_at);
                },
                'last_entered'=> function($model) {
                    return date('d.m.Y',$model->entered_at);
                },
                'business_type',
                'business_id',
                'contact_guid',
                'owner_guid',
                'website',
                'address',
                'lang',
            ];
        }
        // fields for indexing users
        if (Yii::$app->controller->action->id == 'index' && (Yii::$app->user->isAdmin() || Yii::$app->user->isManager())) {
            return [
                'id',
                'email',
                'first_name',
                'middle_name',
                'contact_guid',
                'last_name',
                'company',
                'website',
                'address',
                'status' => function($model) {
                    return User::statuses()[$model->status];
                },
                'projects_number' => function($model) {
                    return $model->role == User::ROLE_CUSTOMER ? count($this->getProjects()) : null;
                },
                'created_at',
                'entered_at',
                'lang',
            ];
        }

        if (Yii::$app->user->isDesigner()) {
            if ($this->record->role == User::ROLE_CUSTOMER) {
                return [
                    'id',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'email',
                    'company',
                    'phone',
                    'mobile_phone',
                    'manager',
                    'lang',
                ];
            }
            if ($this->record->role == User::ROLE_MANAGER) {
                return [
                    'first_name',
                    'middle_name',
                    'last_name',
                    'lang',
                ];
            }
        }

        // Basic fields
        $fields = [
            'email',
            'first_name',
            'middle_name',
            'business_id',
            'address',
            'contact_guid',
            'owner_guid',
            'links_1c' => function() {
                return [
                    'customer_link' => Yii::$app->params['ONE_C_INTEGRATION']['customer_url'],
                    'admin_link' => Yii::$app->params['ONE_C_INTEGRATION']['admin_url'],
                ];
            },
            'provider',
            'website',
            'business_type',
            'last_name',
            'company',
            'role' => function($model) {
                return User::roles()[$model->role];
            },
            'verification_code_sent' => function($model) {
                return !!$model->mobileConfirmationToken;
            },
            'verification_config' => function($model) {
                return $model->mobileConfirmationToken ? $model->mobileConfirmationToken->additional : null;
            },
            'phone',
            'mobile_phone',
            'mobile_phone_verified',
            'lang',
        ];

        if ($this->record->role == User::ROLE_CUSTOMER) {
            $fields[] = 'manager';
        }

        // Admin/manager fields
        if (Yii::$app->user->isAdmin() || Yii::$app->user->isManager()) {
            $fields[] = 'id';
            $fields['status'] = function($model) {
                return User::statuses()[$model->status];
            };
            $fields[] = 'created_at';
            $fields[] = 'updated_at';
            $fields[] = 'entered_at';
            $fields['projects_number'] = function($model) {
                return count($this->getProjects());
            };
            $fields['active_projects_number'] = function($model) {
                return count($this->getActiveProjects());
            };
            $fields['finished_projects_number'] = function($model) {
                return count($this->getFinishedProjects());
            };

            if ($this->record->scenario == User::SCENARIO_VIEW) {
                $fields[] = 'comments';
            }
            $fields[] = 'temporary_pass';
        }

        return $fields;
    }

    /**
     * Get all projects of user
     * @return array
     */
    public function getProjects() {
        return $this->record->projects;
    }

    /**
     * Get all active projects of user
     * @return array
     */
    public function getActiveProjects() {
        $statuses = [
            Project::STATUS_SENT, Project::STATUS_RETURNED, Project::STATUS_DESIGNING,
            Project::STATUS_SPECIFICATION_GRANTED, Project::STATUS_SPECIFICATION_ACCEPTED
        ];
        $activeProjects = [];
        foreach($this->record->projects as $project) {
            if (in_array($project->status, $statuses))
                $activeProjects[] = $project;
        }
        return $activeProjects;
    }

    /**
     * Get all finished projects of user
     * @return array
     */
    public function getFinishedProjects() {
        $finishedProjects = [];
        foreach($this->record->projects as $project) {
            if ($project->status == Project::STATUS_FINISHED)
                $finishedProjects[] = $project;
        }
        return $finishedProjects;
    }
}
