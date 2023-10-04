<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $project string */

$url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['customerAppUrl'], '#' => '/projects/' . $project->id . '/']);
$authorName = implode(' ', [$project->author->first_name, $project->author->middle_name]);
$managerName = $project->author->manager ? implode(' ', [$project->author->manager->first_name, $project->author->manager->middle_name, $project->author->manager->last_name]) : null;
?>

<p>
    Уважаемый <?= Html::encode($authorName) ?>, напоминаем о том, что ваша заявка на проект <?= Html::a(Html::encode($project->title), $url) ?> была создана более четырех дней назад и до сих пор не передана в работу менеджерам. Пожалуйста, не забудьте закончить заполнение всей необходимой информации и передайте проект менеджерам, нажав кнопку "Отправить". Для того, чтобы проект попал в обработку, он должен получить статус "Отправлен".
</p>
<?php if($project->author->manager): ?>
    <p>
        Если вам требуется помощь в правильном оформлении проекта, обратитесь с вопросами к вашему менеджеру <?= Html::encode($managerName) ?>  по адресу <?= $project->author->manager->email ?>.
    </p>
<?php endif; ?>
<hr>
<p>
    Это автоматическое уведомление с сайта
    <br>
    Пожалуйста, не отвечайте на данное письмо.
</p>
