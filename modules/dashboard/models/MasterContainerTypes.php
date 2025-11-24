<?php

namespace dashboard\models;

use Yii;

/**
 * This is the model class for table "master_container_types".
 *
 * @property int $type_id
 * @property string $iso_code
 * @property int $size
 * @property string $type_group
 * @property string|null $description
 *
 * @property ContainerVisits[] $containerVisits
 */
class MasterContainerTypes extends  BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'master_container_types';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['iso_code', 'size', 'type_group'], 'required'],
            [['size'], 'integer'],
            [['iso_code'], 'string', 'max' => 10],
            [['type_group'], 'string', 'max' => 20],
            [['description'], 'string', 'max' => 100],
            [['iso_code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'type_id' => 'Type ID',
            'iso_code' => 'Iso Code',
            'size' => 'Size',
            'type_group' => 'Type Group',
            'description' => 'Description',
        ];
    }

    /**
     * Gets query for [[ContainerVisits]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContainerVisits()
    {
        return $this->hasMany(ContainerVisits::class, ['type_id' => 'type_id']);
    }
}
