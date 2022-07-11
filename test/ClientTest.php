<?php

namespace Horde\Dns\Test;

use Horde\Dns\Client;

class ClientTest extends TestBase
{
    public function testGetDefaultZoneId()
    {
        $stub = $this->createMock(Client::class);
        $stub->method('getDefaultZoneId')
            ->willReturn('ZoneId');

        $actual = $stub->getDefaultZoneId();
        $expected = 'ZoneId';

        $this->assertEquals($expected, $actual);
    }

    public function testGetZoneReturnsNull()
    {
        $stub = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $actual = $stub->getZone();

        $expected = null;

        $this->assertEquals($expected, $actual);
    }

    public function testGetZoneRecordsIsIterable()
    {
        $stub = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $actual = $stub->getZoneRecords();
        $actual = is_iterable($actual);

        $expected = true;
        $this->assertEquals($expected, $actual);
    }

    public function testGetZoneWithoutParameterIsNull()
    {
        $stub = $this->createMock(Client::class);
        $actual = $stub->getZone();

        $expected = null;
        $this->assertEquals($expected, $actual);
    }

    public function testGetSingleRecordWithoutParameterIsNull()
    {
        $stub = $this->createMock(Client::class);
        $actual = $stub->getSingleRecord('test');

        $expected = null;
        $this->assertEquals($expected, $actual);
    }
}
