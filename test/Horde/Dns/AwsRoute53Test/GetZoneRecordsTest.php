<?php

namespace Horde\Dns\AwsRoute53Test;

use Aws\Route53\Route53Client;
use Aws\Result as AwsResult;

use Horde\Dns\AwsRoute53;
use Horde\Dns\RecordPlain;
use Horde\Dns\TestBase;

class GetZoneRecordsTest extends TestBase
{
    public function testReturnsCorrectRecords()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->generateRecordSets(10, $zoneId);

        $client = new AwsRoute53($fakeAwsRoute53Api);
        $ret = $client->getZoneRecords($zoneId);

        $i = 5;
        $expected = new RecordPlain("recordName_".$i, "recordType_".$i, $i, "recordValue_".$i);
        $this->assertEquals($expected, $ret[$i]);
    }

    public function testUseDefaultZoneIdIfEmpty()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->generateRecordSets(120, $zoneId);

        $client = new AwsRoute53($fakeAwsRoute53Api, ["defaultZoneId" => $zoneId]);
        $ret = $client->getZoneRecords();
        $this->assertEquals(120, count($ret));
    }

    public function testHandleMultiPageRequests()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->generateRecordSets(120, $zoneId);

        $client = new AwsRoute53($fakeAwsRoute53Api);
        $ret = $client->getZoneRecords($zoneId);

        $this->assertEquals(120, count($ret));
    }

    public function testHandleMaxResults()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->generateRecordSets(120, $zoneId);

        $client = new AwsRoute53($fakeAwsRoute53Api);
        $ret = $client->getZoneRecords($zoneId, null, 60);

        $this->assertEquals(60, count($ret));
    }

    public function testHandleMaxResultsMultiPage()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->generateRecordSets(240, $zoneId);

        $client = new AwsRoute53($fakeAwsRoute53Api);
        $ret = $client->getZoneRecords($zoneId, null, 222);

        $this->assertEquals(222, count($ret));
    }

    public function testHandleMaxResultsLessThanTotal()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->generateRecordSets(88, $zoneId);

        $client = new AwsRoute53($fakeAwsRoute53Api);
        $ret = $client->getZoneRecords($zoneId, null, 120);

        $this->assertEquals(88, count($ret));
    }

    public function testMethodIsBlacklisted()
    {
        $this->helper->dontCallSdkMethodInBlacklistedMethod("getZoneRecords", "listResourceRecordSets", [], []);
    }
}
