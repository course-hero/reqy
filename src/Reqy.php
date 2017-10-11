<?php

namespace Reqy;

class Reqy
{
    /**
     * @param array $args
     * @param array $types
     */
    protected function typeCheck(array $args, array $types)
    {
        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];
            $type = $types[$i];
            $actualType = gettype($arg);

            if ($type != '*' && $actualType !== $type) {
                throw new \InvalidArgumentException("expected arg #$i to be of type $type, but got $actualType");
            }
        }
    }

    /**
     * @param ReqyErrorLevel|null $level
     * @return Validator
     */
    public function exists(ReqyErrorLevel $level = null)
    {
        $level = $level ?? ReqyErrorLevel::$ERROR;
        return new Validator('exists', $level, function ($value) {
            return $value !== null;
        });
    }

    /**
     * @params ReqyErrorLevel $level = ReqyErrorLevel::$ERROR, $expected
     * @return Validator
     */
    public function equals()
    {
        if (func_num_args() === 1) {
            list($expected) = func_get_args();
            $level = ReqyErrorLevel::$ERROR;
        } elseif (func_num_args() === 2) {
            $this->typeCheck(func_get_args(), [ReqyErrorLevel::class, '*']);
            list($level, $expected) = func_get_args();
        } else {
            throw new \InvalidArgumentException();
        }

        return new Validator('equals', $level, function ($value) use ($expected) {
            return $value === $expected ?: "expected <$expected>, but got <$value>";
        });
    }

    /**
     * @param Validator $validator
     * @return Validator
     */
    public function every(Validator $validator)
    {
        $name = "every <{$validator->getName()}>";
        return new Validator($name, $validator->getLevel(), function ($values) use ($validator) {
            $errorDetails = [];
            foreach ($values as $value) {
                $result = $validator->getPredicate()($value);
                if ($result !== true) {
                    $errorDetails[] = $result;
                }
            }
            return empty($errorDetails) ?: join("\n", $errorDetails);
        });
    }

    /**
     * @param ReqyErrorLevel|null $level
     * @return Validator
     */
    public function odd(ReqyErrorLevel $level = null)
    {
        $level = $level ?? ReqyErrorLevel::$ERROR;
        return new Validator('odd', $level, function ($value) {
            return $value % 2 === 1 ?: "expected <$value> to be odd";
        });
    }

    /**
     * @param ReqyErrorLevel|null $level
     * @return Validator
     */
    public function even(ReqyErrorLevel $level = null)
    {
        $level = $level ?? ReqyErrorLevel::$ERROR;
        return new Validator('even', $level, function ($value) {
            return $value % 2 === 0 ?: "expected <$value> to be even";
        });
    }

    /**
     * @params ReqyErrorLevel $level = ReqyErrorLevel::$ERROR, int $min, int $max = null
     * @return Validator
     */
    public function range()
    {
        if (func_num_args() === 2) {
            $this->typeCheck(func_get_args(), ['integer', 'integer']);
            list($min, $max) = func_get_args();
            $level = ReqyErrorLevel::$ERROR;
        } elseif (func_num_args() === 3) {
            $this->typeCheck(func_get_args(), [ReqyErrorLevel::class, 'int', 'int']);
            list($level, $min, $max) = func_get_args();
        } else {
            throw new \InvalidArgumentException();
        }

        return new Validator('range', $level, function ($value) use ($min, $max) {
            return $value >= $min && ($max === null || $value <= $max)
                ?: "expected <$value> to be in range ($min, $max)";
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
                $reqs[$key] = new Validator('custom validator', ReqyErrorLevel::$ERROR, $value);
            } elseif (!($value instanceof Validator)) {
                $reqs[$key] = $this->equals($value);
            }
        }
    }

    /**
     * @param $object
     * @param array $reqs [string => Validator|array]
     * @param ReqyError[] $errors
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

                $predicate = $validator->getPredicate();
                $result = $predicate($value);
                if ($result !== true) {
                    $errors[] = new ReqyError($validator->getLevel(), $keyConcat, $validator->getName(), $result);
                }
            }
        }
    }

    /**
     * @param $object
     * @param array $reqs
     * @param bool $preprocess
     * @return ReqyError[]
     */
    public function validate($object, array $reqs, bool $preprocess = true): array
    {
        /** @var ReqyError[] $errors */
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
}
