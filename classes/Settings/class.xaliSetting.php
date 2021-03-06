<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xaliSetting
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xaliSetting extends ActiveRecord {

	const DB_TABLE_NAME = "xali_data";
	const CALC_AUTO_MINIMUM_ATTENDANCE = -1;


	static function returnDbTableName() {
		return self::DB_TABLE_NAME;
	}


	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 * @db_is_primary       true
	 */
	protected $id;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           1
	 */
	protected $is_online = 0;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $minimum_attendance = 80;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           1
	 */
	protected $activation = 0;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        date
	 */
	protected $activation_from;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        date
	 */
	protected $activation_to;
	/**
	 * @var array
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           128
	 */
	protected $activation_weekdays;


	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getIsOnline() {
		return $this->is_online;
	}


	/**
	 * @param int $is_online
	 */
	public function setIsOnline($is_online) {
		$this->is_online = $is_online;
	}


	/**
	 * @return int
	 */
	public function getMinimumAttendance() {
		return $this->minimum_attendance;
	}


	/**
	 * @param int $minimum_attendance
	 */
	public function setMinimumAttendance($minimum_attendance) {
		$this->minimum_attendance = $minimum_attendance;
	}


	/**
	 * @return int
	 */
	public function getActivation() {
		return $this->activation;
	}


	/**
	 * @param int $activation
	 */
	public function setActivation($activation) {
		$this->activation = $activation;
	}


	/**
	 * @return int
	 */
	public function getActivationFrom() {
		return $this->activation_from;
	}


	/**
	 * @param int $activation_from
	 */
	public function setActivationFrom($activation_from) {
		$this->activation_from = $activation_from;
	}


	/**
	 * @return int
	 */
	public function getActivationTo() {
		return $this->activation_to;
	}


	/**
	 * @param int $activation_to
	 */
	public function setActivationTo($activation_to) {
		$this->activation_to = $activation_to;
	}


	/**
	 * @return array
	 */
	public function getActivationWeekdays() {
		return $this->activation_weekdays;
	}


	/**
	 * @param int $activation_weekdays
	 */
	public function setActivationWeekdays($activation_weekdays) {
		$this->activation_weekdays = $activation_weekdays;
	}


	/**
	 * @param $field_name
	 *
	 * @return mixed|string
	 */
	public function sleep($field_name) {
		if ($field_name == 'activation_weekdays') {
			return json_encode($this->getActivationWeekdays());
		}

		return parent::sleep($field_name);
	}


	/**
	 * @param $field_name
	 * @param $field_value
	 *
	 * @return mixed
	 */
	public function wakeUp($field_name, $field_value) {
		if ($field_name == 'activation_weekdays' && $field_value) {
			return json_decode($field_value, true);
		}

		return parent::wakeUp($field_name, $field_value);
	}


	/**
	 * @return bool
	 */
	public function createOrDeleteEmptyLists($create, $delete) {
		$begin = $this->getActivationFrom();
		$end = $this->getActivationTo();
		$weekdays = $this->getActivationWeekdays();
		if (!$weekdays || empty($weekdays) || !$begin || !$end) {
			return false;
		}

		// delete empty lists outside defined dates
		if ($delete) {
			foreach (xaliChecklist::where(array( 'obj_id' => $this->getId(), 'last_edited_by' => NULL ))->get() as $checklist) {
				if ($checklist->getChecklistDate() < $begin
					|| $checklist->getChecklistDate() > $end
					|| !in_array(date("D", strtotime($checklist->getChecklistDate())), $weekdays)) {
					$checklist->delete();
				}
			}
		}

		// create empty lists inside defined dates
		if ($create) {
			$begin = new DateTime($begin);
			$end = new DateTime($end);
			$end->setTime(0, 0, 1); // if the time is 00:00:00, the last day will not be included by DatePeriod

			$interval = DateInterval::createFromDateString('1 day');
			$period = new DatePeriod($begin, $interval, $end);

			foreach ($period as $dt) {
				if (in_array($dt->format("D"), $weekdays)) {
					$where = xaliChecklist::where(array( 'checklist_date' => $dt->format('Y-m-d'), 'obj_id' => $this->getId() ));
					if (!$where->hasSets()) {
						$checklist = new xaliChecklist();
						$checklist->setChecklistDate($dt->format('Y-m-d'));
						$checklist->setObjId($this->getId());
						$checklist->create();
					}
				}
			}
		}

		// update LP
		xaliUserStatus::updateUserStatuses($this->id);
	}
}