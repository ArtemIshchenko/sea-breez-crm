<?php

/* @var $this yii\web\View */
/* @var $model app\modules\user\models\ResetPasswordForm */
/* @var $saved bool */

use yii\helpers\Html;
use app\components\widgets\ActiveForm;
use app\components\widgets\Alert;

$this->title = Yii::t('user', 'Замена пароля');
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card p-4">
                <h5 class="mb-3"><?= Html::encode($this->title) ?></h5>
                <?= Alert::widget() ?>
                <?php if (!$saved): ?>
                    <p><?= Yii::t('user', 'Пожалуйста, введите новый пароль:') ?></p>
                    <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>

                        <?= $form->field($model, 'new_password', [
                            'prepend' => '<span class="input-group-text"><i class="fa fa-key"></i></span>'
                        ])->passwordInput(['placeholder' => true])->label(false) ?>

                        <?= $form->field($model, 'new_password_repeat', [
                            'prepend' => '<span class="input-group-text"><i class="fa fa-key"></i></span>'
                        ])->passwordInput(['placeholder' => true])->label(false) ?>

                        <div class="form-group">
                            <?= Html::submitButton(Yii::t('user', 'Сохранить новый пароль'), ['class' => 'btn btn-primary']) ?>
                        </div>

                    <?php ActiveForm::end(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
