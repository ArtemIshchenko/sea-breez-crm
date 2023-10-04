<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $project string */

$url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['designerAppUrl'], '#' => '/projects/' . $project->id . '/']);
?>

<p>Здравствуйте!</p>
<p>
    До дедлайна по проектированию для назначенного вам проекта "<?= Html::encode($project->title) ?>" осталось меньше 48 часов.
</p>
<p>
    <?= Html::a($url, $url) ?>.
</p>
