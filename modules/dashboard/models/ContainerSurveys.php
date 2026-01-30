<?php

namespace dashboard\models;

use Yii;

/**
 * This is the model class for table "container_surveys".
 *
 * @property int $survey_id
 * @property int $visit_id
 * @property string|null $survey_date
 * @property string|null $surveyor_name
 * @property string|null $approval_status
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property SurveyDamages[] $surveyDamages
 * @property ContainerVisits $visit
 */
class ContainerSurveys extends  BaseModel
{
    /**
     * {@inheritdoc}
     */
    public $survey_photo_file;
    public static function tableName()
    {
        return 'container_surveys';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['visit_id'], 'required'],
            [['visit_id', 'created_at', 'updated_at'], 'integer'],
            [['survey_date'], 'safe'],
            [['approval_status'], 'string'],
            [['surveyor_name'], 'string', 'max' => 100],
            [['bill_repairs'], 'boolean'],
            [['survey_photo_path'], 'string'],
            [['survey_photo_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'maxSize' => 5 * 1024 * 1024],

            [['visit_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContainerVisits::class, 'targetAttribute' => ['visit_id' => 'visit_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'survey_id' => 'Survey ID',
            'visit_id' => 'Visit ID',
            'survey_date' => 'Survey Date',
            'surveyor_name' => 'Surveyor Name',
            'approval_status' => 'Approval Status',
            'bill_repairs' => 'Authorize Repairs',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function uploadSurveyPhoto()
    {
        $this->survey_photo_file = \yii\web\UploadedFile::getInstance($this, 'survey_photo_file');
        if ($this->survey_photo_file) {
            $path = Yii::getAlias('@webroot') . '/uploads/survey_photos/';
            if (!is_dir($path)) mkdir($path, 0777, true);

            $fileName = 'SURVEY-' . $this->visit_id . '-' . time() . '.' . $this->survey_photo_file->extension;
            if ($this->survey_photo_file->saveAs($path . $fileName)) {
                return 'uploads/survey_photos/' . $fileName;
            }
        }
        return $this->survey_photo_path;
    }
    /**
     * Gets query for [[SurveyDamages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSurveyDamages()
    {
        return $this->hasMany(SurveyDamages::class, ['survey_id' => 'survey_id']);
    }

    /**
     * Gets query for [[Visit]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVisit()
    {
        return $this->hasOne(ContainerVisits::class, ['visit_id' => 'visit_id']);
    }
}
