<?php

/* @var $this yii\web\View */
/* @var $model app\modules\user\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\ActiveForm;
use app\components\widgets\Alert;

$this->title = 'Login';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card mx-4">
                <div class="card-body p-4">
                    <h4 class="text-muted text-center mb-3"><?= Yii::t('user', 'Добро пожаловать в систему обработки проектных заявок интегрированных комплексов безопасности') ?></h4>
                    <p class="text-muted">
                        <?= Yii::t('user', 'С помощью этого инструмента вы легко сможете зарегистрировать новый проект, предоставить необходимую информацию о нем, узнать статус открытых заявок и обратиться к личному архиву обработанных проектов.') ?>
                    </p>
                    <p class="text-muted">
                        <?= Yii::t('user', 'Использовать систему могут только зарегистрированные пользователи. Пожалуйста, авторизуйтесь или зарегистрируйтесь для начала работы.') ?>
                    </p>
                    <?= Alert::widget() ?>
                    <div class="row mt-5">
                        <div class="col-md-6">
                            <?php $form = ActiveForm::begin([
                                'id' => 'login-form'
                            ]); ?>
                                <?= $form->field($model, 'email', [
                                    'prepend' => '<span class="input-group-text"><i class="fa fa-user"></i></span>'
                                ])->textInput(['placeholder' => true])->label(false) ?>

                                <?= $form->field($model, 'password', [
                                    'prepend' => '<span class="input-group-text"><i class="fa fa-key"></i></span>'
                                ])->passwordInput(['placeholder' => true])->label(false) ?>

                                <?= $form->field($model, 'remember_me')->checkbox([
                                    'labelOptions' => [
                                        'class' => 'text-muted'
                                    ]
                                ]) ?>
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <?= Html::submitButton(Yii::t('user','Войти'), ['class' => 'btn btn-primary px-4']) ?>
                                    <?= Html::a(Yii::t('user','Забыли пароль?'), ['/user/auth/request-password-reset']) ?>
                                </div>
                            <?php ActiveForm::end(); ?>
                        </div>
                        <div class="col-md-6 text-center">
                            <hr class="d-block d-md-none">
                            <?= Html::a(Yii::t('user','Регистрация'), ['/user/auth/register'], ['class' => 'btn btn-primary']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
