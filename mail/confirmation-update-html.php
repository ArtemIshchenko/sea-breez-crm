<?php
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $url */
?>
<div>
    <p>Здравствуйте!</p>
    <p>
        Ваш email был изменен. Чтобы активировать вашу учетную запись, пожалуйста перейдите по ссылке ниже:
        <br />
        <?= Html::a($url, $url) ?>.
    </p>
    <p>C наилучшими пожеланиями, <br />
    </p>
</div>
