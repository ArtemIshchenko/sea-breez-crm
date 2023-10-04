<?php
/* @var $this yii\web\View */
/* @var $project */
/* @var $toRole */

$url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params[$toRole . 'AppUrl'], '#' => '/projects/' . $project->id . '/']);
$files = [];
foreach ($project->files as $file) {
    $files[] = $file->filename;
}
?>
Пользователь без зарегистрированного GUID отправил проект

Название проекта: <?= $project->title ?>
Автор (компания): <?= $project->author->last_name . ' ' . $project->author->first_name . ' ' . $project->author->middle_name ?> (<?= $project->author->company ?>)
Срок сдачи проекта: <?= Yii::$app->formatter->asDate($project->date, 'long') ?>
Заказчик: <?= $project->client ?: 'Нет данных' ?>
Субподрядчик:  <?= $project->subcontractor ?: 'Нет данных' ?>
Проект с доработками: <?= $project->revision_description ? 'да' : 'нет' ?> 
Проект расширяемый: <?= $project->development_prospects ? 'да' : 'нет' ?> 
Прикрепленные файлы: <?= $files ? implode(', ', $files) : 'Нет данных' ?>

<?= $url ?>
