<?php

namespace srag\DataTableUI\AttendanceList\Implementation\Column\Formatter;

use srag\DataTableUI\AttendanceList\Component\Column\Formatter\Formatter;
use srag\DataTableUI\AttendanceList\Implementation\Utils\DataTableUITrait;
use srag\DIC\AttendanceList\DICTrait;

/**
 * Class AbstractFormatter
 *
 * @package srag\DataTableUI\AttendanceList\Implementation\Column\Formatter
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractFormatter implements Formatter
{

    use DICTrait;
    use DataTableUITrait;

    /**
     * AbstractFormatter constructor
     */
    public function __construct()
    {

    }
}
