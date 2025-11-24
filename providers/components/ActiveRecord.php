<?php

namespace  helpers;


class ActiveRecord extends \yii\db\ActiveRecord
{
	use \helpers\traits\Keygen;
	use \helpers\traits\Status;
	public $recordStatus;
	public function behaviors()
	{
		if ($this->tableName() == "{{%active_record}}") {
			return parent::behaviors();
		} else {
			$behaviors = [
				\helpers\behaviors\Delete::class,
				\helpers\behaviors\Creator::class,
			];

			if ($this->hasAttribute('created_at') && $this->hasAttribute('updated_at')) {
				$behaviors[] = \yii\behaviors\TimestampBehavior::class;
			}

			return array_merge(parent::behaviors(), $behaviors);
		}
	}
	public function afterFind()
	{
		if ($this->hasAttribute('status')) {
			$status = ($this->is_deleted == 1) ? $this->is_deleted : $this->status;
			$this->recordStatus = self::badge($status);
		}
		return parent::afterFind();
	}
	public function attributeLabels()
	{
		return [
			'recordStatus' => 'Status',
		];
	}
}
