<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . '/../vendor/autoload.php';

use srag\Notifications4Plugin\AttendanceList\Utils\Notifications4PluginTrait;
use srag\Plugins\AttendanceList\Notification\Ctrl\Notifications4PluginCtrl;
use srag\Plugins\AttendanceList\Notification\Notification\Language\NotificationLanguage;
use srag\Plugins\AttendanceList\Notification\Notification\Notification;

/**
 * Class ilAttendanceListConfigGUI
 *
 * @ilCtrl_IsCalledBy  ilAttendanceListConfigGUI: ilObjComponentSettingsGUIs
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilAttendanceListConfigGUI extends ilPluginConfigGUI {

	use Notifications4PluginTrait;
	const SUBTAB_CONFIG = 'config';
	const SUBTAB_ABSENCE_REASONS = 'absence_reasons';
	const SUBTAB_NOTIFICATION_ABSENCE = Notifications4PluginCtrl::TAB_NOTIFICATION . '_absence';
	const SUBTAB_NOTIFICATION_ABSENCE_REMINDER = Notifications4PluginCtrl::TAB_NOTIFICATION . '_absence_reminder';

	const CMD_STANDARD = 'configure';
	const CMD_ADD_REASON = 'addReason';
	const CMD_SHOW_REASONS = 'showReasons';
	const CMD_CREATE_REASON = 'createReason';
	const CMD_EDIT_REASON = 'editReason';
	const CMD_UPDATE_REASON = 'updateReason';
	const CMD_DELETE_REASON = 'deleteReason';
	const CMD_UPDATE_CONFIG = 'updateConfig';

	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilAttendanceListPlugin
	 */
	protected $pl;
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * ilAttendanceListConfigGUI constructor.
	 */
	public function __construct() {
		global $DIC;
		$tpl = $DIC['tpl'];
		$ilCtrl = $DIC['ilCtrl'];
		$ilToolbar = $DIC['ilToolbar'];
		$ilTabs = $DIC['ilTabs'];
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->toolbar = $ilToolbar;
		$this->tabs = $ilTabs;
		$this->pl = ilAttendanceListPlugin::getInstance();

		// this is for the cron job, since the ILIAS_HTTP_PATH is not initialized in cron context
		if (!xaliConfig::getConfig(xaliConfig::F_HTTP_PATH)) {
			xaliConfig::set(xaliConfig::F_HTTP_PATH, ILIAS_HTTP_PATH);
		}
	}


	function performCommand($cmd) {
		$this->tabs->addSubTab(self::SUBTAB_CONFIG, $this->pl->txt('subtab_'
			. self::SUBTAB_CONFIG), $this->ctrl->getLinkTarget($this, self::CMD_STANDARD));

		$this->tabs->addSubTab(self::SUBTAB_ABSENCE_REASONS, $this->pl->txt('subtab_'
			. self::SUBTAB_ABSENCE_REASONS), $this->ctrl->getLinkTarget($this, self::CMD_SHOW_REASONS));

		$this->ctrl->setParameterByClass(Notifications4PluginCtrl::class, Notifications4PluginCtrl::GET_PARAM, self::notification(Notification::class, NotificationLanguage::class)
			->getNotificationByName(xaliChecklistEntry::NOTIFICATION_NAME)->getId());
		$this->tabs->addSubTab(self::SUBTAB_NOTIFICATION_ABSENCE, $this->pl->txt('subtab_'
			. self::SUBTAB_NOTIFICATION_ABSENCE), $this->ctrl->getLinkTargetByClass(Notifications4PluginCtrl::class, Notifications4PluginCtrl::CMD_EDIT_NOTIFICATION));

		$this->ctrl->setParameterByClass(Notifications4PluginCtrl::class, Notifications4PluginCtrl::GET_PARAM, self::notification(Notification::class, NotificationLanguage::class)
			->getNotificationByName(xaliCron::NOTIFICATION_NAME)->getId());
		$this->tabs->addSubTab(self::SUBTAB_NOTIFICATION_ABSENCE_REMINDER, $this->pl->txt('subtab_'
			. self::SUBTAB_NOTIFICATION_ABSENCE_REMINDER), $this->ctrl->getLinkTargetByClass(Notifications4PluginCtrl::class, Notifications4PluginCtrl::CMD_EDIT_NOTIFICATION));

		switch ($this->ctrl->getNextClass($this)) {
			case strtolower(Notifications4PluginCtrl::class):
				$this->ctrl->forwardCommand(new Notifications4PluginCtrl());
				break;
			default:
				switch ($cmd) {
					case self::CMD_SHOW_REASONS:
						$this->addToolbarButton();
						break;
				}
				$this->{$cmd}();
		}
	}

	protected function configure() {
		$this->tabs->activateSubTab(self::SUBTAB_CONFIG);
		$xaliConfigFormGUI = new xaliConfigFormGUI($this);
		$xaliConfigFormGUI->fillForm();
		$this->tpl->setContent($xaliConfigFormGUI->getHTML());
	}

	protected function showReasons() {
		$this->tabs->activateSubTab(self::SUBTAB_ABSENCE_REASONS);
		$xaliConfigAbsencesTableGUI = new xaliConfigAbsencesTableGUI($this);
		$this->tpl->setContent($xaliConfigAbsencesTableGUI->getHTML());
	}

	protected function addReason() {
		$xaliConfigAbsenceFormGUI = new xaliConfigAbsenceFormGUI($this, new xaliAbsenceReason());
		$this->tpl->setContent($xaliConfigAbsenceFormGUI->getHTML());
	}

	protected function createReason() {
		$xaliConfigAbsenceFormGUI = new xaliConfigAbsenceFormGUI($this, new xaliAbsenceReason());
		$xaliConfigAbsenceFormGUI->setValuesByPost();
		if ($xaliConfigAbsenceFormGUI->saveObject()) {
			ilUtil::sendSuccess($this->pl->txt('msg_saved'), true);
			$this->ctrl->redirect($this, self::CMD_SHOW_REASONS);
		}
		$this->tpl->setContent($xaliConfigAbsenceFormGUI->getHTML());
	}

	protected function editReason() {
		$xaliConfigAbsenceFormGUI = new xaliConfigAbsenceFormGUI($this, new xaliAbsenceReason($_GET['ar_id']));
		$xaliConfigAbsenceFormGUI->fillForm();
		$this->tpl->setContent($xaliConfigAbsenceFormGUI->getHTML());
	}

	/**
	 *
	 */
	protected function updateReason() {
		$xaliConfigAbsenceFormGUI = new xaliConfigAbsenceFormGUI($this, new xaliAbsenceReason($_GET['ar_id']));
		$xaliConfigAbsenceFormGUI->setValuesByPost();
		if ($xaliConfigAbsenceFormGUI->saveObject()) {
			ilUtil::sendSuccess($this->pl->txt('msg_saved'), true);
			$this->ctrl->redirect($this, self::CMD_SHOW_REASONS);
		}
		$this->tpl->setContent($xaliConfigAbsenceFormGUI->getHTML());
	}

	protected function updateConfig() {
		$xaliConfigFormGUI = new xaliConfigFormGUI($this);
		$xaliConfigFormGUI->setValuesByPost();
		if ($xaliConfigFormGUI->saveObject()) {
			ilUtil::sendSuccess($this->pl->txt('msg_saved'), true);
			$this->ctrl->redirect($this, self::CMD_STANDARD);
		}
		$this->tpl->setContent($xaliConfigFormGUI->getHTML());
	}


	protected function addToolbarButton() {
		$button = ilLinkButton::getInstance();
		$button->setUrl($this->ctrl->getLinkTarget($this, self::CMD_ADD_REASON));
		$button->setCaption($this->pl->txt('config_add_new_absence_reason'), false);
		$this->toolbar->addButtonInstance($button);
	}


    /**
     *
     */
	protected function deleteReason() {
        (new xaliAbsenceReason($_GET['ar_id']))->delete();
        ilUtil::sendSuccess($this->pl->txt('msg_deleted'), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_REASONS);
    }
}