<?php
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $url common\models\Url */
?>
<div>
    <p>Здравствуйте!</p>

    <p>Мы получили запрос на изменение пароля. Если его отправили вы, пароль можно изменить воспользовавшись ссылкой:</p>

    <p><?= Html::a($url, $url) ?></p>
</div>
