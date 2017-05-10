<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Services/ActiveRecord/class.ActiveRecord.php';
require_once('./Services/Tracking/classes/class.ilLPStatus.php');
require_once('./Services/Tracking/classes/class.ilLPStatusWrapper.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AttendanceList/classes/class.ilAttendanceListPlugin.php');

/**
 * Class xaliUserStatus
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xaliUserStatus extends ActiveRecord {

	const TABLE_NAME = 'xali_user_status';


	/**
	 * @return string
	 */
	static function returnDbTableName() {
		return self::TABLE_NAME;
	}


	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 * @db_is_primary   true
	 * @db_sequence     true
	 */
	protected $id = 0;

	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 */
	protected $attendancelist_id = 0;

	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 */
	protected $user_id = 0;

	/**
	 * @var string
	 *
	 * @db_has_field    true
	 * @db_fieldtype    timestamp
	 */
	protected $created_at;

	/**
	 * @var string
	 *
	 * @db_has_field    true
	 * @db_fieldtype    timestamp
	 */
	protected $updated_at;

	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 */
	protected $created_user_id = 0;

	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 */
	protected $updated_user_id = 0;

	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 */
	protected $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;


	/**
	 * @var bool
	 */
	protected $status_changed = false;

	/**
	 * @var int
	 */
	protected $old_status;
	/**
	 * @var array
	 */
	protected $checklist_ids;
	/**
	 * @var array
	 */
	protected $attendance_statuses = array();

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getAttendancelistId() {
		return $this->attendancelist_id;
	}


	/**
	 * @param int $attendancelist_id
	 */
	public function setAttendancelistId($attendancelist_id) {
		$this->attendancelist_id = $attendancelist_id;
	}


	/**
	 * @return int
	 */
	public function getUserId() {
		return $this->user_id;
	}


	/**
	 * @param int $user_id
	 */
	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}


	/**
	 * @return string
	 */
	public function getCreatedAt() {
		return $this->created_at;
	}


	/**
	 * @param string $created_at
	 */
	public function setCreatedAt($created_at) {
		$this->created_at = $created_at;
	}


	/**
	 * @return string
	 */
	public function getUpdatedAt() {
		return $this->updated_at;
	}


	/**
	 * @param string $updated_at
	 */
	public function setUpdatedAt($updated_at) {
		$this->updated_at = $updated_at;
	}


	/**
	 * @return int
	 */
	public function getCreatedUserId() {
		return $this->created_user_id;
	}


	/**
	 * @param int $created_user_id
	 */
	public function setCreatedUserId($created_user_id) {
		$this->created_user_id = $created_user_id;
	}


	/**
	 * @return int
	 */
	public function getUpdatedUserId() {
		return $this->updated_user_id;
	}


	/**
	 * @param int $updated_user_id
	 */
	public function setUpdatedUserId($updated_user_id) {
		$this->updated_user_id = $updated_user_id;
	}


	/**
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}


	/**
	 * @param int $status
	 */
	public function setStatus($status)
	{
		if ($status != $this->status) {
			$this->old_status = $this->status;
			$this->status_changed = true;
		}
		$this->status = $status;
	}

	/**
	 * @return bool
	 */
	public function hasStatusChanged()
	{
		return $this->status_changed;
	}


	/**
	 *
	 */
	public function create()
	{
		global $ilUser;

		$this->created_at = date('Y-m-d H:i:s');
		$this->updated_at = date('Y-m-d H:i:s');
		$this->created_user_id = $ilUser->getId();
		$this->updated_user_id = $ilUser->getId();
		parent::create();
	}


	/**
	 *
	 */
	public function update()
	{
		global $ilUser, $ilAppEventHandler;

		$this->updated_at = date('Y-m-d H:i:s');
		$this->updated_user_id = $ilUser->getId();
		parent::update();

		if ($this->hasStatusChanged()) {
			ilLPStatusWrapper::_updateStatus($this->attendancelist_id, $this->user_id);
		}
	}


	/**
	 * @param $status
	 *
	 * @return mixed
	 */
	public function getAttendanceStatuses($status) {
		if (!isset($this->attendance_statuses[$status])) {
			$checklist_ids = $this->getChecklistIds();
			if (!$checklist_ids) {
				$this->attendance_statuses[$status] = 0;
			} else {
				$operators = array(
					'user_id' => '=',
					'status' => '=',
					'checklist_id' => 'IN'
				);

				$this->attendance_statuses[$status] = xaliChecklistEntry::where(array(
					'user_id' => $this->user_id,
					'status' => $status,
					'checklist_id' => $checklist_ids
				), $operators)->count();
			}
		}
		return $this->attendance_statuses[$status];
	}


	/**
	 * @return int
	 */
	public function getReachedPercentage() {
		return round(
			($this->getAttendanceStatuses(xaliChecklistEntry::STATUS_PRESENT)
			+ $this->getAttendanceStatuses(xaliChecklistEntry::STATUS_ABSENT_EXCUSED))
			/ count($this->getChecklistIds())
			* 100
		);
	}


	/**
	 * @return int
	 */
	public function getUnedited() {
		return count($this->getChecklistIds())
			- $this->getAttendanceStatuses(xaliChecklistEntry::STATUS_PRESENT)
			- $this->getAttendanceStatuses(xaliChecklistEntry::STATUS_ABSENT_EXCUSED)
			- $this->getAttendanceStatuses(xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED);
	}


	/**
	 * @param $user_id
	 * @param $attendancelist_id
	 *
	 * @return ActiveRecord|xaliUserStatus
	 */
	public static function getInstance($user_id, $attendancelist_id) {
		$xaliUserStatus = xaliUserStatus::where(array('user_id' => $user_id, 'attendancelist_id' => $attendancelist_id))->first();
		if (!$xaliUserStatus) {
			$xaliUserStatus = new self();
			$xaliUserStatus->setUserId($user_id);
			$xaliUserStatus->setAttendancelistId($attendancelist_id);
		}
		return $xaliUserStatus;
	}


	/**
	 *
	 */
	public function updateLPStatus() {
		$xaliSetting = xaliSetting::find($this->attendancelist_id);
		if ($this->getReachedPercentage() >= $xaliSetting->getMinimumAttendance()) {
			$this->setStatus(ilLPStatus::LP_STATUS_COMPLETED_NUM);
		} else {
			$this->setStatus(ilLPStatus::LP_STATUS_IN_PROGRESS_NUM);
		}
	}


	/**
	 * @param $attendancelist_id
	 */
	public static function updateUserStatuses($attendancelist_id) {
		foreach (ilAttendanceListPlugin::getInstance()->getMembers(ilAttendanceListPlugin::lookupRefId($attendancelist_id)) as $user_id) {
			$user_status = self::getInstance($user_id, $attendancelist_id);
			$user_status->updateLPStatus();
			$user_status->store();
		}
	}

	/**
	 * @return array
	 */
	protected function getChecklistIds() {
		if (!$this->checklist_ids) {
			$this->checklist_ids = array();
			foreach (xaliChecklist::where(array('obj_id' => $this->attendancelist_id))->get() as $checklist) {
				$this->checklist_ids[] = $checklist->getId();
			}
		}
		return $this->checklist_ids;
	}
}