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
            ['password', 'string', 'min' => 8],
            ['password', 'match', 'pattern' => '/^\S*(?=\S*[\W])(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/', 'message' => 'Password Should contain at atleast: 1 number, 1 lowercase letter, 1 uppercase letter and 1 special character'],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => "Passwords don't match"],
        ];
    }
    //Abc#123456
    public function save()
    {
        $user = new User();
        $user->username = $this->username;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        if ($user->save(false)) {
            return $user;
        }
    }
}
