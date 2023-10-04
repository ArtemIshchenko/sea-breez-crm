<?php
/* @var $this yii\web\View */
/* @var $project */
/* @var $toRole */

$url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params[$toRole . 'AppUrl'], '#' => '/projects/' . $project->id . '/']);
?>
Здравствуйте!
Заказчик <?= $project->author->first_name . ' ' . $project->author->last_name ?> вернул спецификацию к проекту "<?= $project->title ?>" на доработку.
<?= $url ?>.
