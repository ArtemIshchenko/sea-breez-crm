<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <?= Html::csrfMetaTags() ?>
    <?= Html::cssFile(YII_DEBUG ? '@web/css/styles.css' : '@web/css/styles.min.css?v=' . filemtime(Yii::getAlias('@webroot/css/styles.min.css'))) ?>
    <title>CCTV and AC - Projects</title>
    <?php $this->head() ?>
</head>
<body class="app flex-row align-items-center">
<?php $this->beginBody() ?>

<?= $content ?>

<?= Html::jsFile(YII_DEBUG ? '@web/js/lib.js' : '@web/js/lib.min.js?v=' . filemtime(Yii::getAlias('@webroot/js/lib.min.js'))) ?>
<?= Html::jsFile(YII_DEBUG ? '@web/js/scripts.js' : '@web/js/scripts.min.js?v=' . filemtime(Yii::getAlias('@webroot/js/scripts.min.js'))) ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
