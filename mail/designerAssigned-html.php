<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $project string */

$url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['designerAppUrl'], '#' => '/projects/' . $project->id . '/']);
?>

<p>Здравствуйте!</p>
<p>
    Вам назначили новый проект "<?= Html::encode($project->title) ?>". Дедлайн проекта: <?= Yii::$app->formatter->asDate($project->designing_deadline) ?>.
</p>
<p>
    <?= Html::a($url, $url) ?>.
</p>
