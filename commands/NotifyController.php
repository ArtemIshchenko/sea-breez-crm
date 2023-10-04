<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\web\Session;
use app\modules\project\models\Project;
use app\modules\main\models\Flag;

class NotifyController extends Controller
{
    /**
     * This command sends notifications about project deadline to designers.
     * @return int Exit code
     */
    public function actionDeadline()
    {
        $projectsToCall = Project::find()
            ->where(['status' => Project::STATUS_DESIGNING])
            ->andWhere(['not', ['designing_deadline' => null]])            
            ->andWhere(['>', 'designing_deadline', time()])
            ->andWhere(['<', 'designing_deadline', time() + 60 * 60 * 48])
            ->with('designer')
            ->all();

        $n = 0;
        foreach ($projectsToCall as $project) {
            $identifier = $project->id;
            if (!Flag::exists(Flag::TYPE_DEADLINE_NOTIFIED, $identifier)) {
                $result = $project->designer->handler->notify('projectDeadline', Yii::t('project', 'Less than 48 hours left to project designing deadline'), [
                    'project' => $project
                ]);
                if ($result) {
                    Flag::add(Flag::TYPE_DEADLINE_NOTIFIED, $identifier);
                    $n++;
                }
            }
        }
        echo "$n project designers were notified about deadline in 24 hours.";
        return ExitCode::OK;
    }

    /**
     * This command cleans removes old deadline flags
     * @return int Exit code
     */
    public function actionCleanDeadlineFlags() {
        $n = Flag::clean(Flag::TYPE_DEADLINE_NOTIFIED, time() - 60 * 60 * 24 - 60);
        echo "$n old 'deadline notified' flags removed.";
        return ExitCode::OK;
    }

    /**
     * Notifies author about old not sent project, where 'old' means from 4 till 5 days.
     * @return int Exit code
     */
    public function actionProjectDelay() {
        $projectsToCall = Project::find()
            ->where(['status' => Project::STATUS_CREATED])
            ->andWhere(['or', ['updated_at' => null], ['<=', 'updated_at', time() - 60 * 60 * 24 * 4]])
            ->with('author')
            ->all();

        $n = 0;
        foreach ($projectsToCall as $project) {
            $identifier = $project->id;
            if (!Flag::exists(Flag::TYPE_PROJECT_SENDING_DELAY, $identifier)) {
                $result = $project->author->handler->notify('projectDelay', Yii::t('project', 'Project delay - {project}', ['project' => $project->title]), [
                    'project' => $project
                ]);
                if ($result) {
                    Flag::add(Flag::TYPE_PROJECT_SENDING_DELAY, $identifier);
                    $n++;
                }
            }
        }
        echo "$n project authors were notified about delay.";
        return ExitCode::OK;
    }
}
