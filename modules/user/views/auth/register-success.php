<?php

/* @var $this yii\web\View */
/* @var $invitation app\modules\user\models\Invitation */
/* @var $oauthOptions array */

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\ActiveForm;
use app\components\widgets\Alert;
use himiklab\yii2\recaptcha\ReCaptcha;

$this->title = Yii::t('user', 'Register');
?>


<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card mx-4">
                <div class="card-body p-4">
                    <h5 class="text-muted text-center mb-4"><?php Yii::t('user', 'Регистрация') ?></h5>
                    <?= Alert::widget()?>
                    <?= Html::a(Yii::t('user', 'Назад к форме входа?'), ['/user/auth/login']);?>
                </div>
            </div>
        </div>
    </div>
</div>
