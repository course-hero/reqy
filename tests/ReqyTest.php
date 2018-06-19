<?php
declare(strict_types=1);

namespace Reqy\Tests;

use CourseHero\Reqy\Issue;
use CourseHero\Reqy\IssueLevel;
use CourseHero\Reqy\Reqy;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

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
        $issues = $this->reqy->validate($object, []);
        self::assertEmpty($issues);
    }

    public function testExists()
    {
        $issues = $this->reqy->validate(['foo' => 'bar'], [
            'foo' => $this->reqy->exists()
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate([], [
            'foo' => $this->reqy->exists()
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('foo', $issues[0]->getKey());
        self::assertEquals('expected field to exist', $issues[0]->getDetails());
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
        $issues = $this->reqy->validate(['foo' => 'bar'], [
            'foo' => $this->reqy->notEmpty()
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate(['foo' => ''], [
            'foo' => $this->reqy->notEmpty()
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('foo', $issues[0]->getKey());
        self::assertEquals('expected field to be non-empty', $issues[0]->getDetails());


        $issues = $this->reqy->validate(['foo' => [1]], [
            'foo' => $this->reqy->notEmpty()
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate(['foo' => []], [
            'foo' => $this->reqy->notEmpty()
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('foo', $issues[0]->getKey());
        self::assertEquals('expected field to be non-empty', $issues[0]->getDetails());
    }

    public function testEquals()
    {
        $object = [
            'foo' => 'bar'
        ];
        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->equals('bar')
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->equals('barz')
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('foo', $issues[0]->getKey());
        self::assertEquals('expected <barz>, but got <bar>', $issues[0]->getDetails());
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
        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->in([1, 2, 3])
        ]);
        self::assertEmpty($issues);


        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->in([1, 3, 5])
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('foo', $issues[0]->getKey());
        self::assertEquals('expected <2> to be in array [1,3,5]', $issues[0]->getDetails());
    }

    public function testEven()
    {
        $issues = $this->reqy->validate(['foo' => 2], [
            'foo' => $this->reqy->even()
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate(['foo' => 3], [
            'foo' => $this->reqy->even()
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('foo', $issues[0]->getKey());
        self::assertEquals('expected 3 to be even', $issues[0]->getDetails());
    }

    public function testOdd()
    {
        $issues = $this->reqy->validate(['foo' => 3], [
            'foo' => $this->reqy->odd()
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate(['foo' => 2], [
            'foo' => $this->reqy->odd()
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('foo', $issues[0]->getKey());
        self::assertEquals('expected 2 to be odd', $issues[0]->getDetails());
    }

    public function testRange()
    {
        $issues = $this->reqy->validate(['cost' => 100], [
            'cost' => $this->reqy->range(0, 200)
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate(['cost' => 100], [
            'cost' => $this->reqy->range(0, 10)
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('cost', $issues[0]->getKey());
        self::assertEquals('expected value to be in range (0, 10), but got 100', $issues[0]->getDetails());
    }

    public function testRangeNoMax()
    {
        $issues = $this->reqy->validate(['cost' => 100], [
            'cost' => $this->reqy->range(50)
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate(['cost' => 0], [
            'cost' => $this->reqy->range(50)
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('cost', $issues[0]->getKey());
        self::assertEquals('expected value to be at least 50, but got 0', $issues[0]->getDetails());
    }

    public function testLength()
    {
        $object = [
            'foo' => 'bar'
        ];
        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->length(3)
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->length(2)
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('foo', $issues[0]->getKey());
        self::assertEquals('expected length to be 2, but got 3', $issues[0]->getDetails());

        $object = [
            'foo' => [1, 2, 3]
        ];
        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->length(3)
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->length(2)
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('foo', $issues[0]->getKey());
        self::assertEquals('expected length to be 2, but got 3', $issues[0]->getDetails());
    }

    public function testLengthRange()
    {
        $object = [
            'foo' => 'bar'
        ];

        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->lengthRange(1, 4)
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->lengthRange(5, 10)
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('foo', $issues[0]->getKey());
        self::assertEquals('expected length to be in range (5, 10), but got 3', $issues[0]->getDetails());

        $object = [
            'foo' => [1, 2, 3]
        ];

        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->lengthRange(1, 4)
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->lengthRange(5, 10)
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('foo', $issues[0]->getKey());
        self::assertEquals('expected length to be in range (5, 10), but got 3', $issues[0]->getDetails());
    }

    public function testWordCount()
    {
        $object = [
            'foo' => 'bar baz qux'
        ];
        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->wordCount(3)
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->wordCount(2)
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('foo', $issues[0]->getKey());
        self::assertEquals('expected word count to be 2, but got 3', $issues[0]->getDetails());
    }

    public function testWordCountRange()
    {
        $object = [
            'foo' => 'bar baz qux'
        ];

        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->wordCountRange(1, 4)
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->wordCountRange(5, 10)
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('foo', $issues[0]->getKey());
        self::assertEquals('expected word count to be in range (5, 10), but got 3', $issues[0]->getDetails());
    }

    public function testEvery()
    {
        $issues = $this->reqy->validate(['numbers' => [1, 5, 11]], [
            'numbers' => $this->reqy->every($this->reqy->odd())
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate(['numbers' => [1, 50, 11]], [
            'numbers' => $this->reqy->every($this->reqy->odd())
        ]);
        self::assertNotEmpty($issues);
        $expected = "issue at index 1, expected 50 to be odd";
        self::assertEquals($expected, $issues[0]->getDetails());

        $issues = $this->reqy->validate(['numbers' => [1, 50, 100]], [
            'numbers' => $this->reqy->every($this->reqy->odd())
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('numbers', $issues[0]->getKey());
        $expected = "issues at indices 1, 2:\n[1] expected 50 to be odd\n[2] expected 100 to be odd";
        self::assertEquals($expected, $issues[0]->getDetails());
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

        $issues = $this->reqy->validate($object, [
            'job' => [
                'title' => $this->reqy->exists(),
                'canHeFixIt' => $this->reqy->equals('yes he can'),
                'yearBegan' => $this->reqy->range(1936, 2017)
            ]
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate($object, [
            'job' => [
                'title' => $this->reqy->exists(),
                'canHeFixIt' => $this->reqy->equals('no he can\'t'),
                'yearBegan' => $this->reqy->range(1936, 2017)
            ]
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('job.canHeFixIt', $issues[0]->getKey());
        self::assertEquals("expected <no he can't>, but got <yes he can>", $issues[0]->getDetails());
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

        $issues = $this->reqy->validate($object, [
            'job' => [
                'title' => $this->reqy->exists(),
                'canHeFixIt' => $this->reqy->equals('no he can\'t'),
                'yearBegan' => $this->reqy->range(1936, 2017)
            ]
        ]);

        $error = $issues[0];
        self::assertEquals('job.canHeFixIt', $error->getKey());
    }

    public function testClosure()
    {
        $issues = $this->reqy->validate(['cost' => 150], [
            'cost' => function($value) {
                return $value % 10 === 0;
            }
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate(['cost' => 155], [
            'cost' => function($value) {
                return $value % 10 === 0;
            }
        ]);
        self::assertNotEmpty($issues);
    }

    public function testLengthWithPreprocessor()
    {
        $object = [
            'foo' => 'bar baz '
        ];

        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->length(6)->setPreprocess(function ($string) {
                return str_replace(' ', '', $string);
            }),
        ]);
        self::assertEmpty($issues);

        $issues = $this->reqy->validate($object, [
            'foo' => $this->reqy->length(8)->setPreprocess(function ($string) {
                return str_replace(' ', '', $string);
            }),
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals('foo', $issues[0]->getKey());
        self::assertEquals('expected length to be 8, but got 6', $issues[0]->getDetails());
    }

    public function testLengthWithMissingValue()
    {
        $issues = $this->reqy->validate([], [
            'cats' => $this->reqy->length(2)
        ]);
        self::assertEquals('expected length to be 2, but value is missing', $issues[0]->getDetails());

        $issues = $this->reqy->validate([], [
            'cats' => $this->reqy->lengthRange(1, 3)
        ]);
        self::assertEquals('expected length to be in range (1, 3), but value is missing', $issues[0]->getDetails());
    }

    public function testLengthWithBadValueThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot determine length of integer');
        $this->reqy->validate([
            'age' => 23
        ], [
            'age' => $this->reqy->length(2)
        ]);
    }

    public function testValidateObjectUsingGetterFns()
    {
        $issues = $this->reqy->validate(new Car(), [
            'name' => 'Lamborghini'
        ]);
        self::assertEmpty($issues);
    }

    public function testIssueContainsBadValue()
    {
        $issues = $this->reqy->validate(['age' => 23], [
            'age' => 35
        ]);
        self::assertNotEmpty($issues);
        self::assertEquals(23, $issues[0]->getValue());
    }

    public function testIssueToString()
    {
        $issue = new Issue(IssueLevel::$ERROR, 'name', 'details');
        self::assertEquals('[ERROR] name: details', (string)$issue);

        $issue = new Issue(IssueLevel::$ERROR, 'name', 'details');
        $issue->setKey('someKey');
        self::assertEquals('[ERROR] key=someKey name: details', (string)$issue);

        $issue = new Issue(IssueLevel::$ERROR, 'name', 'details');
        $issue->setKey('someKey');
        $issue->setValue('someValue');
        self::assertEquals('[ERROR] key=someKey value=someValue name: details', (string)$issue);

        $issue = new Issue(IssueLevel::$ERROR, 'name', 'details');
        $issue->setKey('someKey');
        $issue->setValue(['complex' => ['array' => true]]);
        self::assertEquals('[ERROR] key=someKey value={"complex":{"array":true}} name: details', (string)$issue);
    }
}

class Car
{
   public function getName()
   {
       return 'Lamborghini';
   }
 
   public function getMileage()
   {
       return null;
   }
 
   public function getDescription()
   {
       return 'Cool car';
   }
}
