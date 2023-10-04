<?php
namespace app\components\helpers;

use Yii;
use yii\base\Model;
use yii\web\ServerErrorHttpException;
use yii\web\BadRequestHttpException;

class ErrorHelper
{
    /**
     * Check if model has validation errors.
     * Is used typically after unsuccessful `save()` call.
     * @param  Model  $model   Model to check.
     * @param  string|null $message Exception message to throw. If empty default one will be generated.
     * @return void
     * @throws ServerErrorHttpException if model does not contain validation errors.
     */
    public static function checkModelHasErrors(Model $model, string $message = null) {
        if ($message === null) {
            $message = Yii::t('app', 'Data was not saved for unknown reason. Please contact to administrator about this issue.');
        }
        if (!$model->hasErrors()) {
            throw new ServerErrorHttpException($message);
        }
    }

    /**
     * Generates error message if not provided and throws exception.
     * @param  string|null $message exception message to throw.
     * @return void
     * @throws BadRequestHttpException
     */
    public function throwInputNotLoaded(string $message = null) {
        if ($message === null) {
            $message = Yii::t('app', 'Required input was not provided.');
        }
        throw new BadRequestHttpException($message);
    }
}
