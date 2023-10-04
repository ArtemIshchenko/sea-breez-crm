<?php
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $project */
/* @var $toRole */

$url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params[$toRole . 'AppUrl'], '#' => '/projects/' . $project->id . '/']);
?>
<div>
    <p>Здравствуйте!</p>
    <p>
        <?= Html::encode($project->author->first_name . ' ' . $project->author->last_name) ?> отправил принял спецификацию к проекту "<?= Html::encode($project->title) ?>".
        <br />
        <?= Html::a($url, $url) ?>.
    </p>
</div>
