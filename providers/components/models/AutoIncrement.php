<?php

namespace helpers\models;

use Yii;
use helpers\ActiveRecord;

class AutoIncrement extends ActiveRecord
{
    public static $db = 'db';
    public static $table = 'incrementer';
    protected static $counter = 1;

    public static function tableName()
    {
        return '{{%' . self::$table . '}}';
    }

    public static function getDb()
    {
        return Yii::$app->get(self::$db);
    }

    public function rules()
    {
        return [
            [['year', 'value'], 'required'],
        ];
    }
    public static function generate($type, $save = false, $codeType='')
    {   $type = strtoupper($type);
        $counterValue = null;
        if (($checkCurrent = self::findOne(['year' => date('Y'), 'status' => 10, 'type' => $type])) !== null) {
            static::$counter = ($checkCurrent->value + 1);
            $checkCurrent->value = static::$counter;
            $save ? $checkCurrent->save(false) : null;
            $counterValue = $checkCurrent->value;
        } else {
            if (($checkPrevious = self::findOne(['year' => (date('Y') - 1), 'status' => 10, 'type' => $type])) !== null) {
                $checkPrevious->status = 9;
                $checkPrevious->save(false);
                $counterValue = static::newCode($save, $type);
            } else {
                $counterValue = static::newCode($save, $type);
            }
        }
        return static::codify($counterValue, $codeType);
    }
    protected static function newCode($save, $type)
    {
        $checkCurrent = new self;
        $checkCurrent->year = date('Y');
        $checkCurrent->type = $type;
        $checkCurrent->value = static::$counter;
        $save ? $checkCurrent->save(false) : null;
        return $checkCurrent->value;
    }
    protected static function codify($counterValue, $codeType)
    {
        $finalCode = '';

        if (strpos($codeType, 'd') !== false) {
            $finalCode .= date('d');
        }
        if (strpos($codeType, 'c') !== false) {
            $finalCode .= ceil(date('Y') / 100);
        }
        if (strpos($codeType, 'y') !== false) {
            $finalCode .=  date('y');
        }
        if (strpos($codeType, 'm') !== false) {
            $finalCode .= date('m');
        }
        $finalCode .= str_pad($counterValue, 3, '0', STR_PAD_LEFT);

        return $finalCode;
    }
}
