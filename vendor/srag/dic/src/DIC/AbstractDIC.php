<?php

namespace srag\DIC\AttendanceList\DIC;

use ILIAS\DI\Container;
use srag\DIC\AttendanceList\Database\DatabaseDetector;
use srag\DIC\AttendanceList\Database\DatabaseInterface;

/**
 * Class AbstractDIC
 *
 * @package srag\DIC\AttendanceList\DIC
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractDIC implements DICInterface {

	/**
	 * @var Container
	 */
	protected $dic;


	/**
	 * @inheritDoc
	 */
	public function __construct(Container &$dic) {
		$this->dic = &$dic;
	}


	/**
	 * @inheritdoc
	 */
	public function database() {
		return DatabaseDetector::getInstance($this->databaseCore());
	}
}