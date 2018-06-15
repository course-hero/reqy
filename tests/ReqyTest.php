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
    protected $req;

    public function setUp()
    {
        $this->req = new Reqy();
    }

    public function testCanary()
    {
        $object = [
            'foo' => 'bar'
        ];
        $errors = $this->req->validate($object, []);
        self::assertEmpty($errors);
    }

    public function testExists()
    {
        $errors = $this->req->validate(['foo' => 'bar'], [
            'foo' => $this->req->exists()
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate([], [
            'foo' => $this->req->exists()
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
            'foo' => $this->req->exists()
        ];

        $this->req->preprocess($reqs);
        self::assertEquals($expected, $reqs);
    }

    public function testNotEmpty()
    {
        $errors = $this->req->validate(['foo' => 'bar'], [
            'foo' => $this->req->notEmpty()
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate(['foo' => ''], [
            'foo' => $this->req->notEmpty()
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected field to be non-empty', $errors[0]->getDetails());


        $errors = $this->req->validate(['foo' => [1]], [
            'foo' => $this->req->notEmpty()
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate(['foo' => []], [
            'foo' => $this->req->notEmpty()
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
        $errors = $this->req->validate($object, [
            'foo' => $this->req->equals('bar')
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate($object, [
            'foo' => $this->req->equals('barz')
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
            'foo' => $this->req->equals('bar')
        ];

        $this->req->preprocess($reqs);
        self::assertEquals($expected, $reqs);
    }

    public function testIn()
    {
        $object = [
            'foo' => 2
        ];
        $errors = $this->req->validate($object, [
            'foo' => $this->req->in([1, 2, 3])
        ]);
        self::assertEmpty($errors);


        $errors = $this->req->validate($object, [
            'foo' => $this->req->in([1, 3, 5])
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected <2> to be in array [1,3,5]', $errors[0]->getDetails());
    }

    public function testEven()
    {
        $errors = $this->req->validate(['foo' => 2], [
            'foo' => $this->req->even()
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate(['foo' => 3], [
            'foo' => $this->req->even()
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected 3 to be even', $errors[0]->getDetails());
    }

    public function testOdd()
    {
        $errors = $this->req->validate(['foo' => 3], [
            'foo' => $this->req->odd()
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate(['foo' => 2], [
            'foo' => $this->req->odd()
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected 2 to be odd', $errors[0]->getDetails());
    }

    public function testRange()
    {
        $errors = $this->req->validate(['cost' => 100], [
            'cost' => $this->req->range(0, 200)
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate(['cost' => 100], [
            'cost' => $this->req->range(0, 10)
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
        $errors = $this->req->validate($object, [
            'foo' => $this->req->length(3)
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate($object, [
            'foo' => $this->req->length(2)
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected length to be 2, but got 3', $errors[0]->getDetails());

        $object = [
            'foo' => [1, 2, 3]
        ];
        $errors = $this->req->validate($object, [
            'foo' => $this->req->length(3)
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate($object, [
            'foo' => $this->req->length(2)
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

        $errors = $this->req->validate($object, [
            'foo' => $this->req->lengthRange(1, 4)
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate($object, [
            'foo' => $this->req->lengthRange(5, 10)
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected length to be in range (5, 10), but got 3', $errors[0]->getDetails());

        $object = [
            'foo' => [1, 2, 3]
        ];

        $errors = $this->req->validate($object, [
            'foo' => $this->req->lengthRange(1, 4)
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate($object, [
            'foo' => $this->req->lengthRange(5, 10)
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
        $errors = $this->req->validate($object, [
            'foo' => $this->req->wordCount(3)
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate($object, [
            'foo' => $this->req->wordCount(2)
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

        $errors = $this->req->validate($object, [
            'foo' => $this->req->wordCountRange(1, 4)
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate($object, [
            'foo' => $this->req->wordCountRange(5, 10)
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected word count to be in range (5, 10), but got 3', $errors[0]->getDetails());
    }

    public function testEvery()
    {
        $errors = $this->req->validate(['numbers' => [1, 5, 11]], [
            'numbers' => $this->req->every($this->req->odd())
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate(['numbers' => [1, 50, 11]], [
            'numbers' => $this->req->every($this->req->odd())
        ]);
        self::assertNotEmpty($errors);
        $expected = "error at index 1, expected 50 to be odd";
        self::assertEquals($expected, $errors[0]->getDetails());

        $errors = $this->req->validate(['numbers' => [1, 50, 100]], [
            'numbers' => $this->req->every($this->req->odd())
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

        $errors = $this->req->validate($object, [
            'job' => [
                'title' => $this->req->exists(),
                'canHeFixIt' => $this->req->equals('yes he can'),
                'yearBegan' => $this->req->range(1936, 2017)
            ]
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate($object, [
            'job' => [
                'title' => $this->req->exists(),
                'canHeFixIt' => $this->req->equals('no he can\'t'),
                'yearBegan' => $this->req->range(1936, 2017)
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
                'title' => $this->req->exists(),
                'canHeFixIt' => $this->req->equals('yes he can'),
                'yearBegan' => $this->req->exists()
            ]
        ];

        $this->req->preprocess($reqs);
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

        $errors = $this->req->validate($object, [
            'job' => [
                'title' => $this->req->exists(),
                'canHeFixIt' => $this->req->equals('no he can\'t'),
                'yearBegan' => $this->req->range(1936, 2017)
            ]
        ]);

        $error = $errors[0];
        self::assertEquals('job.canHeFixIt', $error->getKey());
    }

    public function testClosure()
    {
        $errors = $this->req->validate(['cost' => 150], [
            'cost' => function($value) {
                return $value % 10 === 0;
            }
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate(['cost' => 155], [
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

        $errors = $this->req->validate($object, [
            'foo' => $this->req->length(6)->setPreprocess(function ($string) {
                return str_replace(' ', '', $string);
            }),
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate($object, [
            'foo' => $this->req->length(8)->setPreprocess(function ($string) {
                return str_replace(' ', '', $string);
            }),
        ]);
        self::assertNotEmpty($errors);
        self::assertEquals('foo', $errors[0]->getKey());
        self::assertEquals('expected length to be 8, but got 6', $errors[0]->getDetails());
    }
}
