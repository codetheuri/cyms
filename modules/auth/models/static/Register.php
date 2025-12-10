<?php

namespace auth\models\static;

use Yii;
use yii\base\Model;
use auth\models\User;

class Register extends Model
{
    public $username;
    public $password;
    public $confirm_password;


    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required', 'message' => 'Username is required'],
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'An account with similar username already exists.'],

            ['confirm_password', 'required', 'message' => 'This field can not be blank'],
            ['password', 'required', 'message' => 'Please choose a password you can remember'],
            ['password', 'string', 'min' => 4],
            // ['password', 'match', 'pattern' => '/^\S*(?=\S*[\W])(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/', 'message' => 'Password Should contain at atleast: 1 number, 1 lowercase letter, 1 uppercase letter and 1 special character'],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => "Passwords don't match"],
        ];
    }
    //Abc#123456
    // public function save()
    // {
    //     $user = new User();
    //     $user->username = $this->username;
    //     $user->setPassword($this->password);
    //     $user->generateAuthKey();
    //     if ($user->save(false)) {
    //         return $user;
    //     }
    // }
    public function save()
{
    $user = new User();

    // Generate user_id
    $currentYear = date('Y');
    $transaction = Yii::$app->db->beginTransaction();
    try {
        // Lock the incrementer row for this year and type
        $incrementer = Yii::$app->db->createCommand(
            'SELECT * FROM {{%incrementer}} WHERE type = :type AND year = :year FOR UPDATE',
            ['type' => 'user_id', 'year' => $currentYear]
        )->queryOne();

        if (!$incrementer) {
            // If no incrementer exists, initialize it
            Yii::$app->db->createCommand()->insert('{{%incrementer}}', [
                'year' => $currentYear,
                'value' => 1,
                'type' => 'user_id',
                'created_at' => time(),
                'updated_at' => time(),
            ])->execute();
            $user->user_id = $currentYear . '0001';
        } else {
            // Increment the value and set user_id
            $nextValue = $incrementer['value'] + 1;
            Yii::$app->db->createCommand()->update('{{%incrementer}}', [
                'value' => $nextValue,
                'updated_at' => time(),
            ], ['id' => $incrementer['id']])->execute();
            $user->user_id = $currentYear . str_pad($nextValue, 4, '0', STR_PAD_LEFT);
        }

        $transaction->commit();
    } catch (\Exception $e) {
        $transaction->rollBack();
        throw $e;
    }

    // Set other user attributes
    $user->username = $this->username;
    $user->setPassword($this->password);
    $user->generateAuthKey();

    // Save user without validation (if you want validation, pass `true` instead)
    if ($user->save(false)) {
        return $user;
    }

    return null; // Return null if save fails
}

}
