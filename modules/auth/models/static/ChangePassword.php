<?php
namespace auth\models\static;

use Yii;
use yii\base\Model;

class ChangePassword extends Model
{
    public $currentPassword;
    public $newPassword;
    public $confirmPassword;

    private $_user;

    public function __construct($user, $config = [])
    {
        $this->_user = $user; // Pass the user object
        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['currentPassword', 'newPassword', 'confirmPassword'], 'required'],
            ['currentPassword', 'validateCurrentPassword', 'message' => 'Incorrect current password.'],
            ['newPassword', 'string', 'min' => 4],
            ['confirmPassword', 'compare', 'compareAttribute' => 'newPassword', 'message' => 'Passwords do not match.'],
        ];
    }

    public function validateCurrentPassword($attribute, $params)
    {
        if (!$this->_user->validatePassword($this->$attribute)) {
            $this->addError($attribute, 'Incorrect current password.');
        }
    }

    public function changePassword()
    {
        $this->_user->setPassword($this->newPassword);
        $this->_user->updated_at = time();
        return $this->_user->save(false);
    }
}