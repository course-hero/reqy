<?php

namespace Reqy;

/**
 * Class ReqyErrorLevel
 * @package Reqy
 */
class ReqyErrorLevel
{
    /** @var ReqyErrorLevel */
    public static $ERROR;

    /** @var ReqyErrorLevel */
    public static $WARNING;

    public static function initClass()
    {
        self::$ERROR = new ReqyErrorLevel('ERROR');
        self::$WARNING = new ReqyErrorLevel('WARNING');
    }

    /** @var string */
    protected $level;

    /**
     * ReqyErrorLevel constructor.
     * @param string $level
     */
    public function __construct($level)
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->level;
    }
}

// @codingStandardsIgnoreLine
ReqyErrorLevel::initClass();
