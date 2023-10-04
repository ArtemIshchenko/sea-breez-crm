<?php
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $url */
/* @var $user array */
/* @var $managerData string */
?>
<div>
    <p>
        Уведомление о регистрации нового пользователя
    </p>
    <p>
        Адрес электронной почты: <?= Html::encode($user['email']) ?>
        <br />
        ФИО: <?= Html::encode($user['last_name'] . ' ' . $user['first_name'] . ' ' . $user['middle_name']) ?>
        <br />
        Компания: <?= Html::encode($user['company'] ?: 'Нет данных') ?>
        <br />
        Телефон: <?= Html::encode($user['phone'] ?: 'Нет данных') ?>
        <br />
        Менеджер: <?= Html::encode($managerData ?: 'Нет данных') ?>
    </p>
    <p>
        <?= Html::a($url, $url) ?>
    </p>
</div>
