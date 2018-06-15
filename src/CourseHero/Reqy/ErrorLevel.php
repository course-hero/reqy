<?php

namespace CourseHero\Reqy;

/**
 * Class ErrorLevel
 * @package Reqy
 */
class ErrorLevel
{
    /** @var ErrorLevel */
    public static $ERROR;

    /** @var ErrorLevel */
    public static $WARNING;

    public static function initClass()
    {
        self::$ERROR = new ErrorLevel('ERROR');
        self::$WARNING = new ErrorLevel('WARNING');
    }

    /** @var string */
    protected $level;

    /**
     * ErrorLevel constructor.
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
ErrorLevel::initClass();
