<?php

namespace CourseHero\Reqy;

/**
 * Class IssueLevel
 * @package Reqy
 */
class IssueLevel
{
    /** @var IssueLevel */
    public static $ERROR;

    /** @var IssueLevel */
    public static $WARNING;

    public static function initClass()
    {
        self::$ERROR = new IssueLevel('ERROR');
        self::$WARNING = new IssueLevel('WARNING');
    }

    /** @var string */
    protected $level;

    /**
     * IssueLevel constructor.
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
IssueLevel::initClass();
