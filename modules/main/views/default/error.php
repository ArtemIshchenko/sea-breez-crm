<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $exception->statusCode == 404 ? 'Запрашиваемая страница не найдена.' : $name;
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="clearfix">
                <h1 class="float-left display-3 mr-4"><?= Html::encode($exception->statusCode) ?></h1>
                <h4 class="pt-3">Oops! <?= Html::encode($name) ?></h4>
                <p class="text-muted"><?= Html::encode($message) ?></p>
            </div>
        </div>
    </div>
</div>
