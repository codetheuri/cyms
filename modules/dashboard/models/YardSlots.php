<?php

namespace dashboard\models;

use Yii;

/**
 * This is the model class for table "yard_slots".
 *
 * @property int $slot_id
 * @property string $block
 * @property int $row
 * @property int $bay
 * @property int $tier
 * @property string|null $slot_name
 * @property int|null $current_visit_id
 *
 * @property ContainerVisits $currentVisit
 */
class YardSlots extends  BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'yard_slots';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['block', 'row', 'bay'], 'required'],
            [['row', 'bay', 'tier', 'current_visit_id'], 'integer'],
            [['block'], 'string', 'max' => 10],
            [['slot_name'], 'string', 'max' => 20],
            [['slot_name'], 'unique'],
            [['current_visit_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContainerVisits::class, 'targetAttribute' => ['current_visit_id' => 'visit_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'slot_id' => 'Slot ID',
            'block' => 'Block',
            'row' => 'Row',
            'bay' => 'Bay',
            'tier' => 'Tier',
            'slot_name' => 'Slot Name',
            'current_visit_id' => 'Current Visit ID',
        ];
    }

    /**
     * Gets query for [[CurrentVisit]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentVisit()
    {
        return $this->hasOne(ContainerVisits::class, ['visit_id' => 'current_visit_id']);
    }
}
