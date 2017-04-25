<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'class.xaliChecklistGUI.php';
require_once 'class.xaliChecklist.php';
require_once 'class.xaliChecklistEntry.php';
require_once 'Services/User/classes/class.ilObjUser.php';
require_once 'Services/Table/classes/class.ilTable2GUI.php';
/**
 * Class xaliChecklistTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xaliChecklistTableGUI extends ilTable2GUI {

	/**
	 * @var ilAttendanceListPlugin
	 */
	protected $pl;
	/**
	 * @var xaliChecklist
	 */
	protected $checklist;
	/**
	 * @var array
	 */
	protected $users;
	/**
	 * @var bool
	 */
	protected $is_new;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;


	/**
	 * xaliChecklistTableGUI constructor.
	 *
	 * @param xaliChecklistGUI|xaliOverviewGUI $a_parent_obj
	 * @param xaliChecklist    $checklist
	 * @param array            $users
	 */
	public function __construct($a_parent_obj, xaliChecklist $checklist, array $users) {
		global $ilCtrl, $lng;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->pl = ilAttendanceListPlugin::getInstance();
		$this->checklist = $checklist;
		$this->users = $users;
		$this->is_new = ($checklist->getId() == 0);

		parent::__construct($a_parent_obj);

		if (!$this->is_new) {
			$this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));
		}

		$this->setEnableNumInfo(false);
		$this->setRowTemplate('tpl.checklist_row.html', 'Customizing/global/plugins/Services/Repository/RepositoryObject/AttendanceList');
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setLimit(0);
		$this->initColumns();

		$this->initCommands();

		$this->parseData();
	}

	protected function initCommands() {
		$this->addCommandButton('save', $this->lng->txt('save'));
		if ($this->parent_obj instanceof xaliOverviewGUI) {
			$this->addCommandButton('cancel', $this->lng->txt('cancel'));
		}
	}

	protected function initColumns() {
		$this->addColumn($this->pl->txt('table_column_name'));
		$this->addColumn($this->pl->txt('table_column_login'));
		$this->addColumn($this->pl->txt('table_column_status'));
	}

	/**
	 *  parse user ids to data for the table
	 */
	protected function parseData() {
		$data = array();
		foreach ($this->users as $usr_id) {
			$user = new ilObjUser($usr_id);
			$user_data = array();
			$user_data["name"] = $user->getFullname();
			$user_data["login"] = $user->getLogin();
			$user_data["id"] = $user->getId();
			if (isset($_POST['attendance_status'][$user->getId()])) {
				$user_data["checked_" . $_POST['attendance_status'][$user->getId()]] = 'checked';
			} elseif (isset($_POST['attendance_status'])) {
				$user_data["warning"] = $this->pl->txt('warning_not_filled_out');
			} else {
				$checklist_entry = $this->checklist->getEntryOfUser($user->getId());
				if ($status = $checklist_entry->getStatus()) {
					$user_data["checked_$status"] = 'checked';
				}
			}
			$data[] = $user_data;
		}
		$this->setData($data);
	}

	/**
	 * @param array $a_set
	 */
	protected function fillRow($a_set) {
		parent::fillRow($a_set);
		foreach (array('unexcused', 'excused', 'present') as $label) {
			$this->tpl->setVariable('LABEL_'.strtoupper($label), $this->pl->txt('label_'.$label));
		}
	}


	public function fillRowCSV($a_csv, $a_set) {
		unset($a_set['id']);
		foreach ($a_set as $key => $value)
		{
			if ($value == 'checked') {
				$status_id = substr($key, -1);
				$value = $this->pl->txt('status_' . $status_id);
			}
			if(is_array($value))
			{
				$value = implode(', ', $value);
			}
			$a_csv->addColumn(strip_tags($value));
		}
		$a_csv->addRow();
	}


	protected function fillRowExcel($a_worksheet, &$a_row, $a_set) {
		unset($a_set['id']);
		$col = 0;
		foreach ($a_set as $key => $value)
		{
			if ($value == 'checked') {
				$status_id = substr($key, -1);
				$value = $this->pl->txt('status_' . $status_id);
			}
			if(is_array($value))
			{
				$value = implode(', ', $value);
			}
			$a_worksheet->write($a_row, $col, strip_tags($value));
			$col++;
		}
	}
}