<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Test\Unit\Gateway\Helper;

use InvalidArgumentException;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

class SubjectReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->subjectReader = new SubjectReader();
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Helper\SubjectReader::readTransaction
     */
    public function testReadTransactionWithException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Response object does not exist");

        $this->subjectReader->readTransaction([]);
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Helper\SubjectReader::readTransaction
     */
    public function testReadTransaction()
    {
        $object = new \StdClass;
        static::assertEquals($object, $this->subjectReader->readTransaction(['object' => $object]));
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Helper\SubjectReader::readResponseObject
     */
    public function testReadResponseObjectWithException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Response does not exist");

        $this->subjectReader->readResponseObject([]);
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Helper\SubjectReader::readResponseObject
     */
    public function testReadResponseObjectWithExceptionObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Response object does not exist.");

        $this->subjectReader->readResponseObject(['response' => []]);
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Helper\SubjectReader::readResponseObject
     */
    public function testReadResponseObject()
    {
        $object = new \StdClass;
        static::assertEquals($object, $this->subjectReader->readResponseObject(['response' => ['object' => $object]]));
    }
}
