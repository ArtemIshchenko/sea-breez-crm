<?php

namespace app\modules\user\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%user_token}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $type
 * @property string $value
 * @property Json $additional
 * @property int $created_at
 *
 * @property User $user
 */
class Token extends \yii\db\ActiveRecord
{

    const TYPE_CONFIRMATION = 1;
    const TYPE_PASSWORD_RESET = 2;
    const TYPE_MOBILE_CONFIRMATION = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_token}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    static::EVENT_BEFORE_INSERT => ['created_at'],
                    static::EVENT_BEFORE_UPDATE => false
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert) {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert && !$this->value) {
            $this->value = Yii::$app->security->generateRandomString(64);
        }else if($insert && $this->value && $this->type === self::TYPE_MOBILE_CONFIRMATION){
            $this->value = Yii::$app->security->generatePasswordHash($this->value);
            $this->additional = [
                'attempts' => 0,
                'last_attempt' => null,
                'sms_sent'=> time()
            ];
        }

        return true;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    // /**
    //  * @return bool Whether token has expired.
    //  */
    // public function getIsExpired()
    // {
    //     switch ($this->type) {
    //         case self::TYPE_CONFIRMATION:
    //             $expirationTime = Yii::$app->params['user.confirmationTokenExpire'];
    //             break;
    //         case self::TYPE_PASSWORD_RESET:
    //             $expirationTime = Yii::$app->params['user.passwordResetTokenExpire'];
    //             break;
    //         default:
    //             throw new \RuntimeException();
    //     }
    //
    //     return ($this->created_at + $expirationTime) < time();
    // }
}
