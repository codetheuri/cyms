<?php

namespace dashboard\models;

use Yii;
use yii\behaviors\TimestampBehavior;

class MasterRepairCodes extends BaseModel
{
    public static function tableName() { return '{{%master_repair_codes}}'; }

    // public function behaviors() { return [TimestampBehavior::class]; }

    public function rules()
    {
        return [
            [['repair_code', 'description'], 'required'],
            [['standard_hours', 'material_cost', 'labor_cost'], 'number'],
            [['repair_code'], 'string', 'max' => 20],
            [['description'], 'string', 'max' => 255],
            [['repair_code'], 'unique'],
        ];
    }
    
   
    public static function getDropdownList() {
        $models = self::find()->all();
        return \yii\helpers\ArrayHelper::map($models, 'repair_code', function($model){
            return $model->repair_code . ' - ' . $model->description;
        });
    }
    
  
    public static function getJsData() {
        $models = self::find()->all();
        $data = [];
        foreach ($models as $m) {
            $data[$m->repair_code] = [
                'description' => $m->description,
                'hours' => $m->standard_hours,
                'material' => $m->material_cost,
                'labor' => $m->labor_cost,
            ];
        }
        return $data;
    }
}