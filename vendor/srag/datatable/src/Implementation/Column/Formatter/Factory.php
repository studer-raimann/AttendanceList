<?php

namespace srag\DataTableUI\AttendanceList\Implementation\Column\Formatter;

use srag\DataTableUI\AttendanceList\Component\Column\Formatter\Actions\Factory as ActionsFactoryInterface;
use srag\DataTableUI\AttendanceList\Component\Column\Formatter\Factory as FactoryInterface;
use srag\DataTableUI\AttendanceList\Component\Column\Formatter\Formatter;
use srag\DataTableUI\AttendanceList\Implementation\Column\Formatter\Actions\Factory as ActionsFactory;
use srag\DataTableUI\AttendanceList\Implementation\Utils\DataTableUITrait;
use srag\DIC\AttendanceList\DICTrait;

/**
 * Class Factory
 *
 * @package srag\DataTableUI\AttendanceList\Implementation\Column\Formatter
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class Factory implements FactoryInterface
{

    use DICTrait;
    use DataTableUITrait;

    /**
     * @var self|null
     */
    protected static $instance = null;


    /**
     * @return self
     */
    public static function getInstance() : self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Factory constructor
     */
    private function __construct()
    {

    }


    /**
     * @inheritDoc
     */
    public function actions() : ActionsFactoryInterface
    {
        return ActionsFactory::getInstance();
    }


    /**
     * @inheritDoc
     */
    public function chainGetter(array $chain) : Formatter
    {
        return new ChainGetterFormatter($chain);
    }


    /**
     * @inheritDoc
     */
    public function check() : Formatter
    {
        return new CheckFormatter();
    }


    /**
     * @inheritDoc
     */
    public function date() : Formatter
    {
        return new DateFormatter();
    }


    /**
     * @inheritDoc
     */
    public function default() : Formatter
    {
        return new DefaultFormatter();
    }


    /**
     * @inheritDoc
     */
    public function languageVariable(string $prefix) : Formatter
    {
        return new LanguageVariableFormatter($prefix);
    }


    /**
     * @inheritDoc
     */
    public function learningProgress() : Formatter
    {
        return new LearningProgressFormatter();
    }


    /**
     * @inheritDoc
     */
    public function link() : Formatter
    {
        return new LinkFormatter();
    }
}
