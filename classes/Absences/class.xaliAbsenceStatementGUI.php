<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xaliAbsenceStatementGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xaliAbsenceStatementGUI extends xaliGUI {

	const CMD_UPDATE = 'update';
	const CMD_STANDARD = 'show';
	const CMD_DOWNLOAD_FILE = 'downloadFile';


	/**
	 *
	 */
	protected function show() {
		$entry_id = $_GET['entry_id'];
		if (!$entry_id) {
			$entry_id = xaliChecklistEntry::where(array(
				'checklist_id' => $_GET['checklist_id'],
				'user_id' => $_GET['user_id'])
			)->first()->getId();
		}
		$absence = xaliAbsenceStatement::findOrGetInstance($entry_id);
		$xaliAbsenceFormGUI = new xaliAbsenceStatementFormGUI($this, $absence);
		$xaliAbsenceFormGUI->fillForm();
		$this->tpl->setContent($xaliAbsenceFormGUI->getHTML());
	}


	/**
	 *
	 */
	protected function update() {
		$absence = xaliAbsenceStatement::findOrGetInstance($_GET['entry_id']);
		$xaliAbsenceFormGUI = new xaliAbsenceStatementFormGUI($this, $absence);
		$xaliAbsenceFormGUI->setValuesByPost();
		if ($xaliAbsenceFormGUI->saveForm()) {
			$user_id = xaliChecklistEntry::find($_GET['entry_id'])->getUserId();
			xaliUserStatus::updateUserStatus($user_id, $this->parent_gui->obj_id);

			ilUtil::sendSuccess($this->pl->txt('msg_saved'), true);
			$this->cancel();
		}
		$this->tpl->setContent($xaliAbsenceFormGUI->getHTML());
	}

	protected function downloadFile() {
		$file_id = $_GET['file_id'];
		$fileObj = new ilObjFile($file_id, false);
		$fileObj->sendFile();
		exit;
	}

	/**
	 *
	 */
	protected function cancel() {
		if ($back_cmd = $_GET['back_cmd']) {
			$this->ctrl->setParameterByClass(xaliOverviewGUI::class, 'entry_id', $_GET['entry_id']);
			$this->ctrl->redirectByClass(xaliOverviewGUI::class, $back_cmd);
		}
		$this->ctrl->returnToParent($this);
	}
}