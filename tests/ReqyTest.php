<?php
declare(strict_types=1);

namespace Reqy\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Reqy\Reqy;

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

    public function testEvery() {
        $errors = $this->req->validate(['numbers' => [1, 5, 11]], [
            'numbers' => $this->req->every($this->req->odd())
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate(['numbers' => [1, 50, 11]], [
            'numbers' => $this->req->every($this->req->odd())
        ]);
        self::assertNotEmpty($errors);
    }

    public function testRange() {
        $errors = $this->req->validate(['cost' => 100], [
            'cost' => $this->req->range(0, 200)
        ]);
        self::assertEmpty($errors);

        $errors = $this->req->validate(['cost' => 100], [
            'cost' => $this->req->range(0, 10)
        ]);
        self::assertNotEmpty($errors);
    }

    public function testReqsOnSubObject() {
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
}
