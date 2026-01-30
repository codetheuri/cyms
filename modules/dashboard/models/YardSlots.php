<?php

namespace dashboard\models;
use yii\helpers\ArrayHelper;
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
            [['block', 'row','slot_name'], 'required'],
            [['row', 'current_visit_id'], 'integer'],
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

 
    public function getVisit()
    {
        return $this->hasOne(ContainerVisits::class, ['visit_id' => 'current_visit_id']);
    }
    public static function getDropdownList()
    {
        $slots = self::find()->where(['current_visit_id' => null])->all();
        return ArrayHelper::map($slots, 'slot_id', 'slot_name');
    }
    public function parkContainer($visit_id)
    {
        // 1. Check if container is already parked elsewhere and unpark it
        $previousSlot = YardSlots::findOne(['current_visit_id' => $visit_id]);
        if ($previousSlot) {
            $previousSlot->current_visit_id = null;
            $previousSlot->save(false);
        }

        // 2. Park in this slot
        $this->current_visit_id = $visit_id;
        return $this->save(false);
    }
    
    public function unpark()
    {
        $this->current_visit_id = null;
        return $this->save(false);
    }

}
