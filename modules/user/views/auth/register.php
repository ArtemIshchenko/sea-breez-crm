<?php

/* @var $this yii\web\View */
/* @var $invitation app\modules\user\models\Invitation */
/* @var $oauthOptions array */

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\ActiveForm;
use app\components\widgets\Alert;
use himiklab\yii2\recaptcha\ReCaptcha;

$this->title = 'Register';
?>


<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card mx-4">
                <div class="card-body p-4">
                    <h5 class="text-muted text-center mb-4"><?php echo Yii::t('user', 'Регистрация') ?></h5>
                    <?php $hasSuccess = Alert::shouldhideRegisterForm()?>
                    <?= Alert::widget()?>
                    <?php if ($hasSuccess){
                        $model['email'] = '';
                        $model['password'] = '';
                        $model['password_repeat'] = '';
                    } ?>
                    <?php if (!$hasSuccess){
                        $form = ActiveForm::begin([
                            'id' => 'register-form'
                        ]);
                        echo $form->field($model, 'email', [
                            'prepend' => '<span class="input-group-text"><i class="fa fa-user"></i></span>'
                        ])->textInput(['placeholder' => true])->label(false);
                        echo $form->field($model, 'password', [
                            'prepend' => '<span class="input-group-text"><i class="fa fa-key"></i></span>'
                        ])->passwordInput(['placeholder' => true])->label(false);
                        echo $form->field($model, 'password_repeat', [
                            'prepend' => '<span class="input-group-text"><i class="fa fa-key"></i></span>'
                        ])->passwordInput(['placeholder' => true])->label(false);
                        echo $form->field($model, 'accept_conditions')->checkbox([
                            'label' => '<span class="text-muted">' . Yii::t('user', 'Принять условия предоставления услуг и согласиться на обработку персональных данных') . '</span>',
                            'labelOptions' => [
                                'class' => 'form-check-label'
                            ]
                        ]);
                        echo $form->field($model, 'reCaptcha')->widget(ReCaptcha::class)->label(false);
                        echo '<div class="d-flex justify-content-between align-items-center flex-wrap">';
                        echo Html::submitButton(Yii::t('user', 'Зарегистрироваться'), ['class' => 'btn btn-primary px-4 register-btn']);
                        echo Html::a(Yii::t('user', 'Назад к форме входа?'), ['/user/auth/login']);
                        echo '</div>';
                        ActiveForm::end();
                    } ?>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="conditions-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <header class="modal-header">
                <h5 class="modal-title"><?php echo Yii::t('user', 'Условия предоставления услуг') ?></h5>
                <button type="button" aria-label="Close" class="close">×</button>
            </header>
            <div class="modal-body">
                <div class="faq-info">
                    <?php echo $termsOfUse ?>
                </div>
           </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary ok-btn">ОК</button>
            </div>
        </div>
    </div>
</div>
