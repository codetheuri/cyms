<?php

namespace dashboard\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "visit_documents".
 *
 * @property int $doc_id
 * @property int $visit_id
 * @property string|null $doc_type
 * @property string $file_path
 * @property int|null $uploaded_at
 *
 * @property ContainerVisits $visit
 */
class VisitDocuments extends BaseModel
{
    public static function tableName()
    {
        return '{{%visit_documents}}';
    }

    // public function behaviors()
    // {
    //     return [
    //         [
    //             'class' => TimestampBehavior::class,
    //             'createdAtAttribute' => 'uploaded_at',
    //             'updatedAtAttribute' => false, 
    //         ],
    //     ];
    // }

    public function rules()
    {
        return [
            [['visit_id', 'file_path'], 'required'],
            [['visit_id', 'uploaded_at'], 'integer'],
            [['doc_type'], 'string', 'max' => 50],
            [['file_path'], 'string', 'max' => 255],
            [['visit_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContainerVisits::class, 'targetAttribute' => ['visit_id' => 'visit_id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'doc_id' => 'Document ID',
            'visit_id' => 'Visit ID',
            'doc_type' => 'Document Type',
            'file_path' => 'File Path',
            'uploaded_at' => 'Uploaded At',
        ];
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