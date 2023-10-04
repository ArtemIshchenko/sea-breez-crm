<?php
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $project */

$url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['customerAppUrl'], '#' => '/projects/' . $project->id]);
?>
<div>
    <p>Здравствуйте!</p>
    <p>
        Ваш проект <?= Html::encode($project->title) ?> возвращен на доработку.
    </p>
    <?php if ($project->status_message): ?>
        <p>
            Комментарий.
            <br />
            <?= Html::encode($project->status_message) ?>
        </p>
    <?php endif; ?>
    <p>
        <?= Html::a($url, $url) ?>.
    </p>
</div>
