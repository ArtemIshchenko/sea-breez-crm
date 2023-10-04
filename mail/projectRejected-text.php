<?php
/* @var $this yii\web\View */
/* @var $project */

$url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['customerAppUrl'], '#' => '/projects/' . $project->id]);
?>
Здравствуйте!
Ваш проект <?= $project->title ?> отклонен.
<?php if ($project->status_message): ?>
    Комментарий.
    <?= $project->status_message ?>
<?php endif; ?>
<?= $url ?>.
