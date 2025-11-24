<?php

namespace helpers\models;

use helpers\ActiveRecord;

class SystemSettings extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%system_settings}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key', 'label', 'disposition', 'input_type', 'default_value'], 'required'],
            [['current_value', 'default_value', 'input_preload'], 'string'],
            [['key', 'label'], 'string', 'max' => 100],
            [['category', 'input_type'], 'string', 'max' => 20],
            [['key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'key' => 'Key',
            'label' => 'Label',
            'category' => 'Category',
            'disposition' => 'Disposition',
            'input_type' => 'Input Type',
            'current_value' => 'Current Value',
            'default_value' => 'Default Value',
            'input_preload' => 'Input Preload',
        ];
    }
}
