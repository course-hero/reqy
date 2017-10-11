<?php

namespace Reqy;

class Validator
{
    /** @var string */
    public $name;

    /** @var ReqyErrorLevel */
    public $level;

    /** @var \Closure */
    public $predicate;

    /**
     * Validator constructor.
     * @param string $name
     * @param ReqyErrorLevel $level
     * @param \Closure $predicate
     */
    public function __construct($name, ReqyErrorLevel $level, \Closure $predicate)
    {
        $this->name = $name;
        $this->level = $level;
        $this->predicate = $predicate;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return ReqyErrorLevel
     */
    public function getLevel(): ReqyErrorLevel
    {
        return $this->level;
    }

    /**
     * @param ReqyErrorLevel $level
     */
    public function setLevel(ReqyErrorLevel $level)
    {
        $this->level = $level;
    }

    /**
     * @return \Closure
     */
    public function getPredicate(): \Closure
    {
        return $this->predicate;
    }

    /**
     * @param \Closure $predicate
     */
    public function setPredicate(\Closure $predicate)
    {
        $this->predicate = $predicate;
    }
}
