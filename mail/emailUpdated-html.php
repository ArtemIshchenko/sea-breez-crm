<?php
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $user array */
?>
<div>
    <p>Здравствуйте!</p>

    <p>Ваш email адрес был успешно изменен на <?= Html::encode($user['email']) ?>.</p>

    <p>Если вы не отправляли запрос на изменение email адреса, пожалуйста как можно скорее свяжитесь с администратором.</p>
</div>
