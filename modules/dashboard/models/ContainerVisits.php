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
    public $departure_photo_file;

    public static function tableName()
    {
        return '{{%container_visits}}';
    }



    public function rules()
    {
        return [
            // --- TRUCK ONLY FLAG ---
            [['is_truck_only'], 'boolean'],
            [['is_truck_only'], 'default', 'value' => 0],
            [['is_truck_only'], 'safe'],

            // --- CORE REQUIRED FIELDS ---
            [['container_number'], 'required', 'when' => function ($model) {
                return !$model->is_truck_only;
            }, 'whenClient' => "function (attribute, value) {
                return $('#containervisits-is_truck_only').length === 0 || !$('#containervisits-is_truck_only').is(':checked');
            }"],

            // --- GATE IN SCENARIO ---
            [['date_in', 'time_in', 'vehicle_reg_no_in', 'driver_name_in'], 'required', 'on' => self::SCENARIO_GATE_IN],
            [['shipping_line_id', 'container_owner_id', 'container_type_id', 'party_delivering_container'], 'required', 'on' => self::SCENARIO_GATE_IN, 'when' => function ($model) {
                return !$model->is_truck_only;
            }, 'whenClient' => "function (attribute, value) {
                return $('#containervisits-is_truck_only').length === 0 || !$('#containervisits-is_truck_only').is(':checked');
            }"],

            // --- GATE OUT SCENARIO ---
            [['date_out', 'time_out'], 'required', 'on' => self::SCENARIO_GATE_OUT],
            [['vehicle_reg_no_out'], 'required', 'on' => self::SCENARIO_GATE_OUT, 'when' => function ($model) {
                return !$model->is_truck_only;
            }],
            [['gross_weight', 'tare_weight', 'payload'], 'integer', 'min' => 0],
            [['party_delivering_container'], 'string', 'max' => 100],
            // [['gross_weight', 'tare_weight', 'payload'], 'required'],
            [['departure_photo_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'maxSize' => 5 * 1024 * 1024],

            [[
                'status',
                'ticket_no_in',
                'truck_type_in',
                'trailer_reg_no_in',
                'seal_number_in',
                'truck_owner_name_in',
                'truck_owner_contact_in',
                'driver_id_in',
                'yard_clerk_in',
                'ticket_no_out',
                'truck_type_out',
                'trailer_reg_no_out',
                'seal_number_out',
                'driver_name_out',
                'driver_id_out',
                'yard_clerk_out',
                'shipping_agent_name',
                'vessel_name',
                'voyage_number',
                'bl_number',
                'arrival_photo_path'
            ], 'safe'],
            [['date_in', 'time_in'], 'validateFutureDate', 'on' => self::SCENARIO_GATE_IN],
            [['date_out', 'time_out'], 'validateFutureDate', 'on' => self::SCENARIO_GATE_OUT],
            [['shipping_line_id'], 'integer'],
            [['storage_days'], 'number'],
            [['comments_in', 'comments_out'], 'string'],
            [
                ['container_number'],
                'match',
                'pattern' => '/^[A-Z]{4}[0-9]{7}$/',
                'message' => 'Invalid Format. Must be 4 letters followed by 7 digits (e.g., MSCU1234567).',
                'skipOnEmpty' => true,
            ],
            [
                ['container_number'],
                'unique',
                'on' => self::SCENARIO_GATE_IN,
                'targetAttribute' => ['container_number'],
                'skipOnEmpty' => true,
                'when' => function ($model) {
                    return !$model->is_truck_only;
                },
                'whenClient' => "function (attribute, value) {
                    return $('#containervisits-is_truck_only').length === 0 || !$('#containervisits-is_truck_only').is(':checked');
                }",
                'filter' => function ($query) {
                    // Only block if the container exists AND hasn't left yet.
                    // We check for status NOT IN ['GATE_OUT']
                    // If a record exists with 'IN_YARD' or 'SURVEYED', block entry.
                    if ($this->scenario == self::SCENARIO_GATE_IN) {
                        return $query->andWhere(['not in', 'status', ['GATE_OUT']]);
                    }
                },
                'message' => 'This container number is currently active in the yard. You must Gate Out the previous visit first.'
            ],
            [
                ['vehicle_reg_no_in'],
                'unique',
                'on' => self::SCENARIO_GATE_IN,
                'targetAttribute' => ['vehicle_reg_no_in'],
                'skipOnEmpty' => true,
                'filter' => function ($query) {
                    return $query->andWhere(['not in', 'status', ['GATE_OUT']]);
                },
                'message' => 'This truck (Reg No.) is currently active in the yard. You must Gate Out the previous visit first.'
            ],
            [['arrival_photo_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'maxSize' => 5 * 1024 * 1024],
            [['document_files'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, pdf', 'maxFiles' => 5],
        ];
    }

    /**
     * Custom Validator to prevent logging future dates/times.
     */
    public function validateFutureDate($attribute, $params)
    {
        // Don't run if there are already format errors
        if (!$this->hasErrors()) {

            // Determine which date/time we are checking based on the scenario
            if ($this->scenario === self::SCENARIO_GATE_IN) {
                $dateString = $this->date_in . ' ' . ($this->time_in ?: '00:00:00');
                $label = 'Gate IN';
            } else {
                $dateString = $this->date_out . ' ' . ($this->time_out ?: '00:00:00');
                $label = 'Gate OUT';
            }

            // Convert the user's input into a timestamp
            $inputTimestamp = strtotime($dateString);
            $currentTimestamp = time();

            // If the input is greater than the current server time, throw an error
            if ($inputTimestamp > $currentTimestamp) {
                // If it's a few seconds/minutes off due to slight laptop clock drift, you can add a buffer
                // e.g., if ($inputTimestamp > ($currentTimestamp + 300)) for a 5-min grace period.
                // But strictly speaking, block it:
                $this->addError($attribute, "The {$label} Date & Time cannot be in the future.");
            }
        }
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
    public function uploadDeparturePhoto()
    {
        $this->departure_photo_file = \yii\web\UploadedFile::getInstance($this, 'departure_photo_file');
        if ($this->departure_photo_file) {
            $path = Yii::getAlias('@webroot') . '/uploads/container_photos/';
            if (!is_dir($path)) mkdir($path, 0777, true);

            $fileName = 'OUT-' . $this->container_number . '-' . time() . '.' . $this->departure_photo_file->extension;
            if ($this->departure_photo_file->saveAs($path . $fileName)) {
                return 'uploads/container_photos/' . $fileName;
            }
        }
        return $this->departure_photo_path;
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
    public function getVisitDocuments()
    {
        return $this->hasMany(\dashboard\models\VisitDocuments::class, ['visit_id' => 'visit_id']);
    }
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            // ---------------------------------------------------------
            // 1. AUTO-GENERATE GATE IN TICKET
            // Format: TK-IN-{CODE}-{SEQUENCE} (e.g., TK-IN-MSC-00024)
            // ---------------------------------------------------------
            if ($insert && empty($this->ticket_no_in)) {

                // A. Determine Code (Shipping Line or IND)
                $code = 'IND'; // Default to Individual

                if ($this->shipping_line_id) {
                    $line = MasterShippingLines::findOne($this->shipping_line_id);
                    if ($line) {
                        // Get first 3 letters of the code (e.g., MAERSK -> MAE)
                        $code = strtoupper(substr($line->line_code, 0, 3));
                    }
                }

                // B. Generate Incremental Sequence
                // We count how many visits exist and add 1. 
                // (Padded to 5 digits: 1 -> 00001)
                $count = self::find()->count() + 1;
                $sequence = str_pad($count, 5, '0', STR_PAD_LEFT);

                // C. Set the Ticket Number
                $this->ticket_no_in = "TK-IN-{$code}-{$sequence}";
            }

            // ---------------------------------------------------------
            // 2. AUTO-GENERATE GATE OUT TICKET
            // Format: TK-OUT-{CODE}-{SEQUENCE}
            // ---------------------------------------------------------
            if ($this->scenario == self::SCENARIO_GATE_OUT && empty($this->ticket_no_out)) {

                // Reuse the same code logic (Line or IND)
                $code = 'IND';
                if ($this->shipping_line_id) {
                    $line = MasterShippingLines::findOne($this->shipping_line_id);
                    if ($line) {
                        $code = strtoupper(substr($line->line_code, 0, 3));
                    }
                }

                // Generate sequence based on how many have gated out
                $countOut = self::find()->where(['status' => 'GATE_OUT'])->count() + 1;
                $sequence = str_pad($countOut, 5, '0', STR_PAD_LEFT);

                $this->ticket_no_out = "TK-OUT-{$code}-{$sequence}";
            }

            // ---------------------------------------------------------
            // 3. CALCULATE STORAGE DAYS
            // ---------------------------------------------------------
            if ($this->scenario == self::SCENARIO_GATE_OUT && $this->date_in && $this->date_out) {
                $startStr = $this->date_in . ' ' . ($this->time_in ?: '00:00:00');
                $endStr   = $this->date_out . ' ' . ($this->time_out ?: '23:59:59');

                $start = strtotime($startStr);
                $end   = strtotime($endStr);

                $diffSeconds = $end - $start;

                // Apply same logic: Part of a day is a full day
                if ($diffSeconds < 0) {
                    $days = 1;
                } else {
                    $days = floor($diffSeconds / 86400) + 1;
                }

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
    /**
     * Get DataProvider for active containers with flags/comments
     */
    public static function getFlaggedDataProvider()
    {
        $query = self::find()
            ->where(['not', ['comments_in' => null]])
            ->andWhere(['!=', 'comments_in', ''])
            ->andWhere(['not in', 'status', ['GATE_OUT']]); // Only show active yard flags

        return new \yii\data\ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
            'pagination' => ['pageSize' => 15],
        ]);
    }

    /**
     * Get count of active flagged containers for the Tab Badge
     */
    public static function getFlaggedCount()
    {
        return self::find()
            ->where(['not', ['comments_in' => null]])
            ->andWhere(['!=', 'comments_in', ''])
            ->andWhere(['not in', 'status', ['GATE_OUT']])
            ->count();
    }
}
