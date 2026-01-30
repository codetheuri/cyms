<?php

namespace dashboard\models;

use Yii;

/**
 * This is the model class for table "survey_damages".
 *
 * @property int $damage_id
 * @property int $survey_id
 * @property string|null $repair_code
 * @property string $description
 * @property int|null $quantity
 * @property float|null $labor_cost
 * @property float|null $material_cost
 * @property float|null $total_cost
 *
 * @property ContainerSurveys $survey
 */
class SurveyDamages extends  BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'survey_damages';
    }

    /**
     * {@inheritdoc}
     */
    // public function rules()
    // {
    //     return [
    //         [['survey_id', 'description'], 'required'],
    //         [['survey_id', 'quantity'], 'integer'],
    //         [['labor_cost', 'material_cost', 'total_cost'], 'number'],
    //         [['repair_code'], 'string', 'max' => 20],
    //         [['description'], 'string', 'max' => 255],
    //         [['survey_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContainerSurveys::class, 'targetAttribute' => ['survey_id' => 'survey_id']],
    //     ];
    // }
public function rules()
{
    return [
        // REMOVE 'survey_id' from here if it exists!
        [['description'], 'required'], 
        
        // It is okay to keep it as an integer rule, just not required
        [['survey_id', 'quantity'], 'integer'],
        [['labor_cost', 'material_cost', 'total_cost'], 'number'],
        [['repair_code'], 'string', 'max' => 20],
        [['description'], 'string', 'max' => 255],
        
        // Ensure the foreign key exists (optional, usually handled by DB)
        [['survey_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContainerSurveys::class, 'targetAttribute' => ['survey_id' => 'survey_id']],
    ];
}
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'damage_id' => 'Damage ID',
            'survey_id' => 'Survey ID',
            'repair_code' => 'Repair Code',
            'description' => 'Description',
            'quantity' => 'Quantity',
            'labor_cost' => 'Labor Cost',
            'material_cost' => 'Material Cost',
            'total_cost' => 'Total Cost',
        ];
    }

    /**
     * Gets query for [[Survey]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSurvey()
    {
        return $this->hasOne(ContainerSurveys::class, ['survey_id' => 'survey_id']);
    }
}
