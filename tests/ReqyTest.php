<?php
declare(strict_types=1);

namespace Reqy\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use CourseHero\Reqy\Reqy;

class ReqyTest extends TestCase
{
    public static function assertEmpty($array, $message = '')
    {
        Assert::assertEmpty($array, $message ?: "expected empty array, got: " . json_encode($array));
    }

    /** @var Reqy */
    protected $reqy;

    public function setUp()
    {
        $this->reqy = new Reqy();
    }

    public function testCanary()
    {
        $object = [
            'foo' => 'bar'
        ];
        $errors = $this->reqy->validate($object, []);
        self::assertEmpty($errors);
    }

    public function testExists()
    {
        $errors = $this->reqy->validate(['foo' => 'bar'], [
            'foo' => $this->reqy->exists()
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate([], [
            'foo' => $this->reqy->exists()
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected field to exist', $errors[0]->getDetails());
    }

    public function testExistsPreprocess()
    {
        $reqs = [
            'foo'
        ];
        $expected = [
            'foo' => $this->reqy->exists()
        ];

        $this->reqy->preprocess($reqs);
        self::assertEquals($expected, $reqs);
    }

    public function testNotEmpty()
    {
        $errors = $this->reqy->validate(['foo' => 'bar'], [
            'foo' => $this->reqy->notEmpty()
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate(['foo' => ''], [
            'foo' => $this->reqy->notEmpty()
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected field to be non-empty', $errors[0]->getDetails());


        $errors = $this->reqy->validate(['foo' => [1]], [
            'foo' => $this->reqy->notEmpty()
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate(['foo' => []], [
            'foo' => $this->reqy->notEmpty()
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected field to be non-empty', $errors[0]->getDetails());
    }

    public function testEquals()
    {
        $object = [
            'foo' => 'bar'
        ];
        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->equals('bar')
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->equals('barz')
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected <barz>, but got <bar>', $errors[0]->getDetails());
    }

    public function testEqualsPreprocess()
    {
        $reqs = [
            'foo' => 'bar'
        ];
        $expected = [
            'foo' => $this->reqy->equals('bar')
        ];

        $this->reqy->preprocess($reqs);
        self::assertEquals($expected, $reqs);
    }

    public function testIn()
    {
        $object = [
            'foo' => 2
        ];
        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->in([1, 2, 3])
        ]);
        self::assertEmpty($errors);


        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->in([1, 3, 5])
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected <2> to be in array [1,3,5]', $errors[0]->getDetails());
    }

    public function testEven()
    {
        $errors = $this->reqy->validate(['foo' => 2], [
            'foo' => $this->reqy->even()
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate(['foo' => 3], [
            'foo' => $this->reqy->even()
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected 3 to be even', $errors[0]->getDetails());
    }

    public function testOdd()
    {
        $errors = $this->reqy->validate(['foo' => 3], [
            'foo' => $this->reqy->odd()
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate(['foo' => 2], [
            'foo' => $this->reqy->odd()
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected 2 to be odd', $errors[0]->getDetails());
    }

    public function testRange()
    {
        $errors = $this->reqy->validate(['cost' => 100], [
            'cost' => $this->reqy->range(0, 200)
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate(['cost' => 100], [
            'cost' => $this->reqy->range(0, 10)
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('cost', $errors[0]->getKey());
        self::assertEquals('expected value to be in range (0, 10), but got 100', $errors[0]->getDetails());
    }

    public function testLength()
    {
        $object = [
            'foo' => 'bar'
        ];
        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->length(3)
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->length(2)
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected length to be 2, but got 3', $errors[0]->getDetails());

        $object = [
            'foo' => [1, 2, 3]
        ];
        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->length(3)
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->length(2)
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected length to be 2, but got 3', $errors[0]->getDetails());
    }

    public function testLengthRange()
    {
        $object = [
            'foo' => 'bar'
        ];

        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->lengthRange(1, 4)
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->lengthRange(5, 10)
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected length to be in range (5, 10), but got 3', $errors[0]->getDetails());

        $object = [
            'foo' => [1, 2, 3]
        ];

        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->lengthRange(1, 4)
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->lengthRange(5, 10)
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected length to be in range (5, 10), but got 3', $errors[0]->getDetails());
    }

    public function testWordCount()
    {
        $object = [
            'foo' => 'bar baz qux'
        ];
        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->wordCount(3)
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->wordCount(2)
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected word count to be 2, but got 3', $errors[0]->getDetails());
    }

    public function testWordCountRange()
    {
        $object = [
            'foo' => 'bar baz qux'
        ];

        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->wordCountRange(1, 4)
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->wordCountRange(5, 10)
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected word count to be in range (5, 10), but got 3', $errors[0]->getDetails());
    }

    public function testEvery()
    {
        $errors = $this->reqy->validate(['numbers' => [1, 5, 11]], [
            'numbers' => $this->reqy->every($this->reqy->odd())
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate(['numbers' => [1, 50, 11]], [
            'numbers' => $this->reqy->every($this->reqy->odd())
        ]);
        self::assertNotEmpty($errors);
        $expected = "error at index 1, expected 50 to be odd";
        self::assertEquals($expected, $errors[0]->getDetails());

        $errors = $this->reqy->validate(['numbers' => [1, 50, 100]], [
            'numbers' => $this->reqy->every($this->reqy->odd())
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('numbers', $errors[0]->getKey());
        $expected = "errors at indices 1, 2:\n[1] expected 50 to be odd\n[2] expected 100 to be odd";
        self::assertEquals($expected, $errors[0]->getDetails());
    }

    public function testReqsOnSubObject()
    {
        $object = [
            'name' => 'bob',
            'job' => [
                'title' => 'builder',
                'canHeFixIt' => 'yes he can',
                'yearBegan' => 1998
            ]
        ];

        $errors = $this->reqy->validate($object, [
            'job' => [
                'title' => $this->reqy->exists(),
                'canHeFixIt' => $this->reqy->equals('yes he can'),
                'yearBegan' => $this->reqy->range(1936, 2017)
            ]
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate($object, [
            'job' => [
                'title' => $this->reqy->exists(),
                'canHeFixIt' => $this->reqy->equals('no he can\'t'),
                'yearBegan' => $this->reqy->range(1936, 2017)
            ]
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('job.canHeFixIt', $errors[0]->getKey());
        self::assertEquals("expected <no he can't>, but got <yes he can>", $errors[0]->getDetails());
    }

    public function testPreprocessReqsOnSubObject()
    {
        $reqs = [
            'job' => [
                'title',
                'canHeFixIt' => 'yes he can',
                'yearBegan'
            ]
        ];

        $expected = [
            'job' => [
                'title' => $this->reqy->exists(),
                'canHeFixIt' => $this->reqy->equals('yes he can'),
                'yearBegan' => $this->reqy->exists()
            ]
        ];

        $this->reqy->preprocess($reqs);
        self::assertEquals($expected, $reqs);
    }

    public function testErrorsOnSubObjectsShouldHaveCompoundKeys()
    {
        $object = [
            'name' => 'bob',
            'job' => [
                'title' => 'builder',
                'canHeFixIt' => 'yes he can',
                'yearBegan' => 1998
            ]
        ];

        $errors = $this->reqy->validate($object, [
            'job' => [
                'title' => $this->reqy->exists(),
                'canHeFixIt' => $this->reqy->equals('no he can\'t'),
                'yearBegan' => $this->reqy->range(1936, 2017)
            ]
        ]);

        $error = $errors[0];
        self::assertEquals('job.canHeFixIt', $error->getKey());
    }

    public function testClosure()
    {
        $errors = $this->reqy->validate(['cost' => 150], [
            'cost' => function($value) {
                return $value % 10 === 0;
            }
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate(['cost' => 155], [
            'cost' => function($value) {
                return $value % 10 === 0;
            }
        ]);
        self::assertNotEmpty($errors);
    }

    public function testLengthWithPreprocessor()
    {
        $object = [
            'foo' => 'bar baz '
        ];

        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->length(6)->setPreprocess(function ($string) {
                return str_replace(' ', '', $string);
            }),
        ]);
        self::assertEmpty($errors);

        $errors = $this->reqy->validate($object, [
            'foo' => $this->reqy->length(8)->setPreprocess(function ($string) {
                return str_replace(' ', '', $string);
            }),
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected length to be 8, but got 6', $errors[0]->getDetails());
    }

    public function testLengthWithMissingValue()
    {
        $errors = $this->reqy->validate([], [
            'cats' => $this->reqy->length(2)
        ]);
        self::assertEquals('expected length to be 2, but value is missing', $errors[0]->getDetails());

        $errors = $this->reqy->validate([], [
            'cats' => $this->reqy->lengthRange(1, 3)
        ]);
        self::assertEquals('expected length to be 2, but value is missing', $errors[0]->getDetails());
    }
}
