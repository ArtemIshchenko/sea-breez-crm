<?php
namespace app\modules\user\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidParamException;
use app\modules\user\models\User;
use app\modules\user\models\Token;
/**
 * Password reset form
 */
class ResetPasswordForm extends Model
{
    public $new_password;

    public $new_password_repeat;

    /**
     * @var \common\models\User
     */
    public $user;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['new_password', 'new_password_repeat'], 'required'],
            [['new_password'], 'string', 'min' => 8],
            [['new_password_repeat'], 'compare', 'compareAttribute' => 'new_password']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'new_password' => Yii::t('user', 'New password'),
            'new_password_repeat' => Yii::t('user', 'New password repeat')
        ];
    }

    /**
     * Resets password.
     *
     * @return bool if password was reset.
     */
    public function resetPassword()
    {
        $user = $this->user;
        $user->setPassword($this->new_password);
        if ($user->save(false)) {
            $user->handler->removeToken(Token::TYPE_PASSWORD_RESET);
            return true;
        }
        return false;
    }
}
