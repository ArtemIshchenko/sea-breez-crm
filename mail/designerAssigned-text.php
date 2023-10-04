<?php

/* @var $this yii\web\View */
/* @var $project string */

$url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['designerAppUrl'], '#' => '/projects/' . $project->id . '/']);
?>

Здравствуйте!
Вам назначили новый проект "<?= $project->title ?>".  Дедлайн проекта: <?= Yii::$app->formatter->asDate($project->designing_deadline) ?>.
<?= $url ?>.
