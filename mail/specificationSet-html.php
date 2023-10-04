<?php
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $project */

$url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['customerAppUrl'], '#' => '/projects/' . $project->id]);
?>
<div>
    <p>Здравствуйте!</p>
    <p>
        К вашему проекту "<?= Html::encode($project->title) ?>" была подана спецификация.
    </p>
    <p>
        <?= Html::a($url, $url) ?>.
    </p>
</div>
