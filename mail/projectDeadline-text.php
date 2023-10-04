<?php

/* @var $this yii\web\View */
/* @var $project string */

$url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['designerAppUrl'], '#' => '/projects/' . $project->id . '/']);
?>

Здравствуйте!
До дедлайна по проектированию для назначенного вам проекта "<?= $project->title ?>" осталось меньше 48 часов.
<?= $url ?>.
