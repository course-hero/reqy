<?php

namespace CourseHero\Reqy;

class Issue implements \JsonSerializable
{
    /** @var IssueLevel */
    protected $level;

    /** @var string */
    protected $key;

    protected $value;

    /** @var string */
    protected $validationName;

    /** @var string */
    protected $details;

    /**
     * Issue constructor.
     * @param IssueLevel $level
     * @param string $key
     * @param string $validationName
     * @param string $details
     */
    public function __construct(IssueLevel $level, string $validationName, string $details)
    {
        $this->setLevel($level);
        $this->setValidationName($validationName);
        $this->setDetails($details);
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
     */
    public function setLevel(IssueLevel $level)
    {
        $this->level = $level;
    }

    public function getKey(): ?string
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

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
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
        if (is_string($this->value)) {
            $valueFormatted = $this->value;
        } else if (isset($this->value)) {
            $valueFormatted = json_encode($this->value);
        }
        
        return "[$this->level] " . (isset($this->key) ? "key={$this->key} " : '') . (isset($valueFormatted) ? "value=$valueFormatted " : '') . "{$this->validationName}: {$this->details}";
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
