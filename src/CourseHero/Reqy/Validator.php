<?php

namespace CourseHero\Reqy;

class Validator
{
    /** @var string */
    protected $name;

    /** @var IssueLevel */
    protected $level;

    /** @var \Closure */
    protected $predicate;

    /** @var  \Closure|null */
    protected $preprocess;

    /**
     * Validator constructor.
     * @param string $name
     * @param IssueLevel $level
     * @param \Closure $predicate
     * @param \Closure $preprocess
     */
    public function __construct(string $name, IssueLevel $level, \Closure $predicate, \Closure $preprocess = null)
    {
        $this->setName($name);
        $this->setLevel($level);
        $this->setPredicate($predicate);
        $this->setPreprocess($preprocess);
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
     * @return IssueLevel
     */
    public function getLevel(): IssueLevel
    {
        return $this->level;
    }

    /**
     * @param IssueLevel $level
     * @return Validator
     */
    public function setLevel(IssueLevel $level): Validator
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
    public function setPreprocess(\Closure $preprocess = null): Validator
    {
        $this->preprocess = $preprocess;

        return $this;
    }
}
