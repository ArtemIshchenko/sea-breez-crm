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
        <?= Html::encode($project->author->first_name . ' ' . $project->author->last_name) ?> отозвал свой проект.
        <br />
        <?php if ($project->status_message) {
            echo 'Сообщение пользователя: "' . Html::encode($project->status_message) . '"';
        } ?>
        <br />
        <?= Html::a($url, $url) ?>.
    </p>
</div>
