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
        Заказчик <?= Html::encode($project->author->first_name . ' ' . $project->author->last_name) ?> вернул спецификацию к проекту "<?= Html::encode($project->title) ?>" на доработку.
        <br />
        <?= Html::a($url, $url) ?>.
    </p>
</div>
