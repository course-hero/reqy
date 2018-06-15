<?php

namespace CourseHero\Reqy;

class Reqy
{
    /** @var ErrorLevel */
    protected $defaultErrorLevel;

    /**
     * Reqy constructor.
     * @param ErrorLevel $defaultErrorLevel
     */
    public function __construct(ErrorLevel $defaultErrorLevel = null)
    {
        $this->defaultErrorLevel = $defaultErrorLevel ?? ErrorLevel::$ERROR;
    }

    /**
     * @return Validator
     */
    public function exists(): Validator
    {
        return new Validator('exists', $this->defaultErrorLevel, function ($value) {
            return $value !== null ?: "expected field to exist";
        });
    }

    public function notEmpty(): Validator
    {
        return new Validator('not empty', $this->defaultErrorLevel, function ($value) {
            return $value !== null && $this->getLength($value) > 0 ?: "expected field to be non-empty";
        });
    }

    /**
     * @params $expected
     * @return Validator
     */
    public function equals($expected): Validator
    {
        return new Validator('equals', $this->defaultErrorLevel, function ($value) use ($expected) {
            return $value === $expected ?: "expected <$expected>, but got <$value>";
        });
    }

    /**
     * @param array $options
     * @return Validator
     */
    public function in(array $options): Validator
    {
        return new Validator('in', $this->defaultErrorLevel, function ($value) use ($options) {
            return in_array($value, $options) ?: "expected <$value> to be in array " . json_encode($options);
        });
    }

    /**
     * @return Validator
     */
    public function even(): Validator
    {
        return new Validator('even', $this->defaultErrorLevel, function ($value) {
            return $value % 2 === 0 ?: "expected $value to be even";
        });
    }

    /**
     * @return Validator
     */
    public function odd(): Validator
    {
        return new Validator('odd', $this->defaultErrorLevel, function ($value) {
            return $value % 2 === 1 ?: "expected $value to be odd";
        });
    }

    /**
     * @param int $min
     * @param int $max = null
     * @return Validator
     */
    public function range(int $min, int $max = null): Validator
    {
        return new Validator('range', $this->defaultErrorLevel, function ($value) use ($min, $max) {
            return $this->validateRange('value', $value, $min, $max);
        });
    }

    /**
     * @param int $expected
     * @return Validator
     */
    public function length(int $expected): Validator
    {
        return new Validator('length', $this->defaultErrorLevel, function ($value) use ($expected) {
            $len = $this->getLength($value);

            return $len === $expected ?: "expected length to be $expected, but got $len";
        });
    }

    /**
     * @param int $min
     * @param int|null $max
     * @return Validator
     */
    public function lengthRange(int $min, int $max = null): Validator
    {
        return new Validator('length range', $this->defaultErrorLevel, function ($value) use ($min, $max) {
            $len = $this->getLength($value);

            return $this->validateRange('length', $len, $min, $max);
        });
    }

    /**
     * @param int $expected
     * @return Validator
     */
    public function wordCount(int $expected): Validator
    {
        return new Validator('word count', $this->defaultErrorLevel, function ($value) use ($expected) {
            $wc = str_word_count($value);

            return $wc === $expected ?: "expected word count to be $expected, but got $wc";
        });
    }

    /**
     * @param int $min
     * @param int|null $max
     * @return Validator
     */
    public function wordCountRange(int $min, int $max = null): Validator
    {
        return new Validator('word count range', $this->defaultErrorLevel, function ($value) use ($min, $max) {
            $wc = str_word_count($value);

            return $this->validateRange('word count', $wc, $min, $max);
        });
    }

    /**
     * @param Validator $validator
     * @return Validator
     */
    public function every(Validator $validator): Validator
    {
        $name = "every <{$validator->getName()}>";
        return new Validator($name, $validator->getLevel(), function ($values) use ($validator) {
            $errorLines = [];
            foreach ($values as $i => $value) {
                $result = $validator->getPredicate()($value);
                if ($result !== true) {
                    $errorLines[$i] = $result;
                }
            }

            if (empty($errorLines)) {
                return true;
            }

            if (count($errorLines) === 1) {
                $index = array_keys($errorLines)[0];
                $error = $errorLines[$index];
                return "error at index {$index}, {$error}";
            }

            $indicesListed = join(', ', array_keys($errorLines));
            $errorsListed = array_map(function ($i, $value) {
                return "[$i] $value";
            }, array_keys($errorLines), $errorLines);

            return "errors at indices $indicesListed:\n" . join("\n", $errorsListed);
        });
    }

    /**
     * @param array $reqs [string => Validator|array|*|null]
     */
    public function preprocess(array& $reqs): void
    {
        foreach ($reqs as $key => &$value) {
            if (is_int($key)) {
                // kv pair with integer key means it's not a key => value mapping
                $reqs[$value] = $this->exists();
                unset($reqs[$key]);
            } elseif (is_array($value)) {
                $this->preprocess($value);
            } elseif ($value instanceof \Closure) {
                $reqs[$key] = new Validator('custom validator', ErrorLevel::$ERROR, $value);
            } elseif (!($value instanceof Validator)) {
                $reqs[$key] = $this->equals($value);
            }
        }
    }

    protected function validateRange(string $validator, int $value, int $min, int $max = null)
    {
        if ($max === null) {
            return $value >=  $min ?: "expected $validator to be at least $min, but got $value";
        }

        return $value >= $min && $value <= $max ?: "expected $validator to be in range ($min, $max), but got $value";
    }

    /**
     * @param $value
     * @return int
     */
    protected function getLength($value): int
    {
        if (is_string($value)) {
            return strlen($value);
        } elseif (is_array($value)) {
            return count($value);
        }

        throw new \InvalidArgumentException('Cannot determine length of ' . get_class($value));
    }

    /**
     * @param $object
     * @param array $reqs [string => Validator|array]
     * @param Error[] $errors
     * @param string $baseKey
     */
    protected function validateImpl($object, array $reqs, array& $errors, string $baseKey = '')
    {
        foreach ($reqs as $key => $req) {
            $keyConcat = $baseKey ? "$baseKey.$key" : $key;
            $value = $this->resolve($object, $key);

            if (is_array($req)) {
                $this->validateImpl($value, $req, $errors, $keyConcat);
            } else {
                /** @var Validator $validator */
                $validator = $req;

                $preprocess = $validator->getPreprocess();
                if ($preprocess) {
                    $value = $preprocess($value);
                }

                $predicate = $validator->getPredicate();
                $result = $predicate($value);
                if ($result !== true) {
                    $errors[] = new Error($validator->getLevel(), $keyConcat, $validator->getName(), $result);
                }
            }
        }
    }

    /**
     * @param $object
     * @param array $reqs
     * @param bool $preprocess
     * @return Error[]
     */
    public function validate($object, array $reqs, bool $preprocess = true): array
    {
        /** @var Error[] $errors */
        $errors = [];

        if ($preprocess) {
            $this->preprocess($reqs);
        }

        $this->validateImpl($object, $reqs, $errors);
        return $errors;
    }

    /**
     * @param $object
     * @param $key
     * @return *
     */
    protected function resolve($object, $key)
    {
        if (key_exists($key, $object)) {
            return $object[$key];
        } else {
            return null;
        }
    }

    /**
     * @return ErrorLevel
     */
    public function getDefaultErrorLevel(): ErrorLevel
    {
        return $this->defaultErrorLevel;
    }

    /**
     * @param ErrorLevel $defaultErrorLevel
     */
    public function setDefaultErrorLevel(ErrorLevel $defaultErrorLevel)
    {
        $this->defaultErrorLevel = $defaultErrorLevel;
    }
}
