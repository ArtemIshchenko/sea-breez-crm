<?php
/* @var $this yii\web\View */
/* @var $project */

$url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['customerAppUrl'], '#' => '/projects/' . $project->id]);
?>
Здравствуйте!
К вашему проекту "<?= $project->title ?>" была подана спецификация.
<?= $url ?>.
