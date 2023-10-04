<?php

/* @var $this yii\web\View */
/* @var $project string */

$url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params['customerAppUrl'], '#' => '/projects/' . $project->id . '/']);
$authorName = implode(' ', [$project->author->first_name, $project->author->middle_name]);
$managerName = $project->author->manager ? implode(' ', [$project->author->manager->first_name, $project->author->manager->middle_name, $project->author->manager->last_name]) : null;
?>

Уважаемый <?= $authorName ?>, напоминаем о том, что ваша заявка на проект <?= $project->title ?> была создана более четырех дней назад и до сих пор не передана в работу менеджерам. Пожалуйста, не забудьте закончить заполнение всей необходимой информации и передайте проект менеджерам, нажав кнопку "Отправить". Для того, чтобы проект попал в обработку, он должен получить статус "Отправлен".

<?php if($project->author->manager): ?>
    Если вам требуется помощь в правильном оформлении проекта, обратитесь с вопросами к вашему менеджеру <?= $managerName ?>  по адресу <?= $project->author->manager->email ?>.
<?php endif; ?>
--------------------------------------------------
Это автоматическое уведомление с сайта
Пожалуйста, не отвечайте на данное письмо.
