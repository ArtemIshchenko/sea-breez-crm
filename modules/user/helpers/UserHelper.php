<?php

namespace app\modules\user\helpers;

use Yii;
use app\modules\user\models\User;
use app\modules\user\models\Token;

/**
 * User helper class
 */
class UserHelper
{
    /**
     * Returns url to frontend application depending on user role or null if user is guest.
     * @param  User $user User model object. If empty url for current user wil be returned.
     * @return string|null
    */
    public static function getAppUrl($user = null) {
        if (!$user) {
            $user = Yii::$app->user->identity;
        }
        if (!$user) {
            return null;
        }
        switch (Yii::$app->user->identity->role) {
            case User::ROLE_ADMIN:
                return Yii::$app->params['adminAppUrl'];
            case User::ROLE_DESIGNER:
                return Yii::$app->params['designerAppUrl'];
            case User::ROLE_MANAGER:
                return Yii::$app->params['managerAppUrl'];
            default:
                return Yii::$app->params['customerAppUrl'];
        }
    }

    /**
     * Returns relation name of token with given type.
     * @param  int $type
     * @return string
     */
    public static function getTokenRelationByType($type) {
        $relation = null;
        switch ($type) {
            case Token::TYPE_CONFIRMATION:
                $relation = 'confirmationToken';
                break;
            case Token::TYPE_PASSWORD_RESET:
                $relation = 'passwordResetToken';
                break;
        }
        return $relation;
    }

    /**
     * Get list of administrator user emails
     * @return string[]
     */
    public static function administratorEmails() {
        return array_keys(User::find()
            ->select('email')
            ->indexBy('email')
            ->where(['role' => User::ROLE_ADMIN, 'status' => User::STATUS_ACTIVE])
            ->all());
    }


    public function userExsists($id, $onlyActive = true) {
        $query = User::find()->where(['id' => $id]);
        if ($onlyActive)
            $query->andWhere(['status' => User::STATUS_ACTIVE]);
        return $query->exists();
    }
}
