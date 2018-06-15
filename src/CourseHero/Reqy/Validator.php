<?php

namespace CourseHero\Reqy;

class Validator
{
    /** @var string */
    protected $name;

    /** @var ErrorLevel */
    protected $level;

    /** @var \Closure */
    protected $predicate;

    /** @var  \Closure|null */
    protected $preprocess;

    /**
     * Validator constructor.
     * @param string $name
     * @param ErrorLevel $level
     * @param \Closure $predicate
     * @param \Closure $preprocess
     */
    public function __construct($name, ErrorLevel $level, \Closure $predicate, \Closure $preprocess = null)
    {
        $this->name = $name;
        $this->level = $level;
        $this->predicate = $predicate;
        $this->preprocess = $preprocess;
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
     * @return Validator
     */
    public function setName(string $name): Validator
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return ErrorLevel
     */
    public function getLevel(): ErrorLevel
    {
        return $this->level;
    }

    /**
     * @param ErrorLevel $level
     * @return Validator
     */
    public function setLevel(ErrorLevel $level): Validator
    {
        $this->level = $level;

        return $this;
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
     * @return Validator
     */
    public function setPredicate(\Closure $predicate): Validator
    {
        $this->predicate = $predicate;

        return $this;
    }

    /**
     * @return \Closure|null
     */
    public function getPreprocess()
    {
        return $this->preprocess;
    }

    /**
     * @param \Closure $preprocess
     * @return Validator
     */
    public function setPreprocess(\Closure $preprocess): Validator
    {
        $this->preprocess = $preprocess;

        return $this;
    }
}
