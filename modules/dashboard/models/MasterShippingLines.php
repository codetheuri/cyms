<?php

namespace dashboard\models;

use Yii;

/**
 * This is the model class for table "master_shipping_lines".
 *
 * @property int $line_id
 * @property string $line_code
 * @property string $line_name
 * @property string|null $contact_email
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property ContainerVisits[] $containerVisits
 */
class MasterShippingLines extends  BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'master_shipping_lines';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['line_code', 'line_name'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['line_code'], 'string', 'max' => 20],
            [['line_name', 'contact_email'], 'string', 'max' => 100],
            [['line_code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'line_id' => 'Line ID',
            'line_code' => 'Line Code',
            'line_name' => 'Line Name',
            'contact_email' => 'Contact Email',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[ContainerVisits]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContainerVisits()
    {
        return $this->hasMany(ContainerVisits::class, ['line_id' => 'line_id']);
    }
}
