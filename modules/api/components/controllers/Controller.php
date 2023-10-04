<?php

namespace app\modules\api\components\controllers;

use Yii;

class Controller extends \yii\rest\Controller
{

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = true;

    /**
     * @inheritdoc
     */
     public function behaviors() {
         $behaviors = parent::behaviors();

         // use web access filter specified in modules instead of authenticator
         if (isset($behaviors['authenticator']))
             unset($behaviors['authenticator']);

         // Response format already set in `Api` module
         if (isset($behaviors['contentNegotiator']))
            unset($behaviors['contentNegotiator']);

         return $behaviors;
     }


     /**
      * Checks the privilege of the current user.
      *
      * This method should be overridden to check whether the current user has the privilege
      * to run the specified action.
      * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
      *
      * @param string $action the ID of the action to be executed
      * @param array $params additional parameters
      * @throws ForbiddenHttpException if the user does not have access
      */
     public function checkAccess($action, $params = []) {

     }

}
