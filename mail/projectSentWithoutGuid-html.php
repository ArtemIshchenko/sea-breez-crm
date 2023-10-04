<?php
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $project */
/* @var $toRole */

$url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->params[$toRole . 'AppUrl'], '#' => '/projects/' . $project->id . '/']);
$files = [];
foreach ($project->files as $file) {
    $files[] = $file->filename;
}
?>
<div>
    <p>Пользователь без зарегистрированного GUID отправил проект </p>
    <p>
        Название проекта: <?= Html::encode($project->title) ?>
        <br />
        Автор (компания): <?= Html::encode($project->author->last_name . ' ' . $project->author->first_name . ' ' . $project->author->middle_name) ?> (<?= Html::encode($project->author->company) ?>)
        <br />
        Срок сдачи проекта: <?= Yii::$app->formatter->asDate($project->date, 'long') ?>
        <br />
        Заказчик: <?= Html::encode($project->client ?: 'Нет данных') ?>
        <br />
        Субподрядчик:  <?= Html::encode($project->subcontractor ?: 'Нет данных') ?>
        <br />
        Проект с доработками: <?= $project->revision_description ? 'да' : 'нет' ?> 
        <br />
        Проект расширяемый: <?= $project->development_prospects ? 'да' : 'нет' ?> 
        <br />
        Прикрепленные файлы: <?= $files ? Html::encode(implode(', ', $files)) : 'Нет данных' ?>
    </p>
    <p>
        <?= Html::a($url, $url) ?>.
    </p>
</div>
