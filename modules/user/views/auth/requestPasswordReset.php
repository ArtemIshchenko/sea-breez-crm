<?php

/* @var $this yii\web\View */
/* @var $model app\modules\user\models\PasswordResetRequestForm */

use yii\helpers\Html;
use app\components\widgets\ActiveForm;
use app\components\widgets\Alert;

$this->title = Yii::t('user', 'Запрос на замену пароля');
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card p-4">
                <h5 class="text-muted text-center mb-4"><?= Html::encode($this->title) ?></h5>
                <?= Alert::widget() ?>
                <p class="text-muted"><?= Yii::t('user', Yii::t('user','Пожалуйста введите ваш email адрес.')) ?></p>
                <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>

                    <?= $form->field($model, 'email', [
                        'prepend' => '<span class="input-group-text"><i class="fa fa-envelope"></i></span>'
                    ])->textInput(['placeholder' => true])->label(false) ?>

                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <?= Html::submitButton(Yii::t('user','Поменять пароль'), ['class' => 'btn btn-primary']) ?>
                        <?= Html::a(Yii::t('user','Назад к форме входа'), ['/user/auth/login']) ?>
                    </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
