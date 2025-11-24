<?php

namespace dashboard\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\web\UploadedFile;
use dashboard\models\ContainerSurveys;
use dashboard\models\MasterShippingLines; 

class ContainerVisits extends  BaseModel
{
    const SCENARIO_GATE_IN = 'gate_in';
    const SCENARIO_GATE_OUT = 'gate_out';

    public $arrival_photo_file; 
    public $document_files;

    public static function tableName()
    {
        return '{{%container_visits}}';
    }



    public function rules()
    {
        return [
            // --- CORE REQUIRED FIELDS ---
            [['container_number'], 'required'],

            // --- GATE IN SCENARIO ---
            [['date_in', 'time_in', 'vehicle_reg_no_in', 'driver_name_in', 'shipping_line_id', 'container_owner_id', 'container_type_id'], 'required', 'on' => self::SCENARIO_GATE_IN],
            
            // --- GATE OUT SCENARIO ---
            [['date_out', 'time_out', 'vehicle_reg_no_out', 'destination'], 'required', 'on' => self::SCENARIO_GATE_OUT],

            [[
                'status', 'ticket_no_in', 'truck_type_in', 'trailer_reg_no_in', 'seal_number_in', 
                'truck_owner_name_in', 'truck_owner_contact_in', 'driver_id_in', 'yard_clerk_in',
                'ticket_no_out', 'truck_type_out', 'trailer_reg_no_out', 'seal_number_out', 
                'truck_owner_name_out', 'truck_owner_contact_out', 'driver_name_out', 'driver_id_out', 'yard_clerk_out',
                'shipping_agent_name', 'vessel_name', 'voyage_number', 'bl_number', 'arrival_photo_path'
            ], 'safe'],

            [['storage_days', 'shipping_line_id'], 'integer'],
             [['comments_in'], 'string'],
            
            [['arrival_photo_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'maxSize' => 5 * 1024 * 1024],
            [['document_files'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, pdf', 'maxFiles' => 5],
        ];
    }

   
    public function uploadArrivalPhoto()
    {
        $this->arrival_photo_file = UploadedFile::getInstance($this, 'arrival_photo_file');

        if ($this->arrival_photo_file) {
            $uploadDir = Yii::getAlias('@webroot') . '/uploads/container_photos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            if ($this->arrival_photo_path && file_exists(Yii::getAlias('@webroot') . '/' . $this->arrival_photo_path)) {
                @unlink(Yii::getAlias('@webroot') . '/' . $this->arrival_photo_path);
            }

            $fileName = $this->container_number . '-' . time() . '.' . $this->arrival_photo_file->extension;
            if ($this->arrival_photo_file->saveAs($uploadDir . $fileName)) {
                return 'uploads/container_photos/' . $fileName;
            }
        }
        return $this->arrival_photo_path;
    }

  public function uploadDocuments($type = 'Gate In Doc')
    {
        $this->document_files = \yii\web\UploadedFile::getInstances($this, 'document_files');
        if ($this->document_files) {
            $path = Yii::getAlias('@webroot') . '/uploads/documents/';
            if (!is_dir($path)) mkdir($path, 0777, true);

            foreach ($this->document_files as $file) {
                $fileName = $this->container_number . '_' . $type . '_' . uniqid() . '.' . $file->extension;
                if ($file->saveAs($path . $fileName)) {
                    // You need a VisitDocuments model for this
                    $doc = new \dashboard\models\VisitDocuments(); 
                    $doc->visit_id = $this->visit_id;
                    $doc->file_path = 'uploads/documents/' . $fileName;
                    $doc->doc_type = $type; // Saves "Gate In Doc" or "Gate Out Doc"
                    $doc->uploaded_at = time();
                    $doc->save();
                }
            }
            return true;
        }
        return false;
    }
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Auto-generate Ticket In
            if ($insert && empty($this->ticket_no_in)) {
                $this->ticket_no_in = 'IN-' . date('ymd') . '-' . rand(100, 999);
            }
            
            // Auto-generate Ticket Out (Only on Gate OUT scenario)
            if ($this->scenario == self::SCENARIO_GATE_OUT && empty($this->ticket_no_out)) {
                $this->ticket_no_out = 'OUT-' . date('ymd') . '-' . rand(100, 999);
            }

            // Calculate Storage Days (Only on Gate OUT scenario)
            if ($this->scenario == self::SCENARIO_GATE_OUT && $this->date_in && $this->date_out) {
                $in = new \DateTime($this->date_in);
                $out = new \DateTime($this->date_out);
                $diff = $in->diff($out);
                $days = $diff->days;
                if ($days < 1) $days = 1; 
                $this->storage_days = $days;
            }
            return true;
        }
        return false;
    }
    
   public function getContainerOwner()
    {
        return $this->hasOne(MasterContainerOwners::class, ['owner_id' => 'container_owner_id']);
    }
    public function getShippingLine()
    {
        return $this->hasOne(MasterShippingLines::class, ['line_id' => 'shipping_line_id']);
    }
    public function getContainerType()
    {
        return $this->hasOne(MasterContainerTypes::class, ['type_id' => 'container_type_id']);
    }
    public function getContainerSurvey()
    {
        return $this->hasOne(ContainerSurveys::class, ['visit_id' => 'visit_id']);
    }

    public function getIsSurveyComplete()
    {
        // Returns true if a survey exists and is APPROVED
        return $this->getContainerSurvey()->andWhere(['approval_status' => 'APPROVED'])->exists();
    }
}