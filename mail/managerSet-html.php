<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $url string */
/* @var $user array */
?>

<p>Здравствуйте!</p>
<p>
    Вас назначили менеджером нового клиента <?= Html::encode(trim($user['first_name'] . ' ' . $user['last_name'])) ?>:
</p>
<p>
    <?= Html::a($url, $url) ?>.
</p>
