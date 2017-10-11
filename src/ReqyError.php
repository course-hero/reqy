<?php

namespace Reqy;

class ReqyError implements \JsonSerializable
{
    /** @var ReqyErrorLevel */
    protected $level;

    /** @var string */
    protected $key;

    /** @var string */
    protected $validationName;

    /** @var string */
    protected $details;

    /**
     * ReqyError constructor.
     * @param ReqyErrorLevel $level
     * @param string $key
     * @param string $validationName
     * @param string $details
     */
    public function __construct(ReqyErrorLevel $level, string $key, string $validationName, string $details)
    {
        $this->level = $level;
        $this->key = $key;
        $this->validationName = $validationName;
        $this->details = $details;
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
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getValidationName(): string
    {
        return $this->validationName;
    }

    /**
     * @param string $validationName
     */
    public function setValidationName(string $validationName)
    {
        $this->validationName = $validationName;
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return $this->details;
    }

    /**
     * @param string $details
     */
    public function setDetails(string $details)
    {
        $this->details = $details;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "[{$this->level}] <{$this->key}> {$this->validationName}: {$this->details}";
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        $vars['level'] = (string) $this->level;
        return $vars;
    }
}
