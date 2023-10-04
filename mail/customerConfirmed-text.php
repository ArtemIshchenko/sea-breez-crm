<?php
/* @var $this yii\web\View */
/* @var $url string */
/* @var $user array */
/* @var $managerData string */
?>
Уведомление о регистрации нового пользователя

Адрес электронной почты: <?= $user['email'] ?>
ФИО: <?= trim($user['last_name'] . ' ' . $user['first_name'] . ' ' . $user['middle_name']) ?>
Компания: <?= $user['company'] ?: 'Нет данных' ?>
Телефон: <?= $user['phone'] ?: 'Нет данных' ?>
Менеджер: <?= $managerData ?: 'Нет данных' ?>

<?= $url ?>
