<?php

namespace app\modules\api\components\actions;

use Yii;
use yii\web\ServerErrorHttpException;

class DeleteAction extends \yii\rest\Action
{
    /**
     * Deletes a model. Softly if possible.
     * @param mixed $id id of the model to be deleted.
     * @throws ServerErrorHttpException on failure.
     */
    public function run($id)
    {
        $model = $this->findModel($id);
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }
        if ($model->hasAttribute('deleted_at')) {
            $model->deleted_at = time();
            if (!$model->save(false, ['deleted_at'])) {
                throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
            }
        }
        else if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }
        Yii::$app->getResponse()->setStatusCode(204);
    }
}
