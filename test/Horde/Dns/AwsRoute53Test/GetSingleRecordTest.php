<?php

namespace Horde\Dns\AwsRoute53Test;

use Aws\Route53\Route53Client;
use Aws\Result as AwsResult;

use Horde\Dns\AwsRoute53;
use Horde\Dns\RecordPlain;
use Horde\Dns\TestBase;

class GetSingleRecordTest extends TestBase
{
    public function testReturnsCorrectRecord()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->generateRecordSets(10, $zoneId);

        $i = 4;
        $client = new AwsRoute53($fakeAwsRoute53Api);
        $ret = $client->getSingleRecord("recordName_" . $i, $zoneId);

        $expected = new RecordPlain("recordName_".$i, "recordType_".$i, $i, "recordValue_".$i);
        $this->assertEquals($expected, $ret);
    }

    public function testUseDefaultZoneIdIfEmpty()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->generateRecordSets(120, $zoneId);

        $i = 4;
        $client = new AwsRoute53($fakeAwsRoute53Api, ["defaultZoneId" => $zoneId]);
        $ret = $client->getSingleRecord("recordName_" . $i);

        $expected = new RecordPlain("recordName_".$i, "recordType_".$i, $i, "recordValue_".$i);
        $this->assertEquals($expected, $ret);
    }

    public function testGetRecordOnLaterPages()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->generateRecordSets(333, $zoneId);

        $i = 303;
        $client = new AwsRoute53($fakeAwsRoute53Api);
        $ret = $client->getSingleRecord("recordName_" . $i, $zoneId);

        $expected = new RecordPlain("recordName_".$i, "recordType_".$i, $i, "recordValue_".$i);
        $this->assertEquals($expected, $ret);
    }

    public function testReturnNullIfNotExactNameMatch()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->generateRecordSets(10, $zoneId);

        $client = new AwsRoute53($fakeAwsRoute53Api);
        $ret = $client->getSingleRecord("recordName_" . 88, $zoneId);

        $expected = null;
        $this->assertEquals($expected, $ret);
    }

    public function testReturnNullNoneFound()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->addZone($zoneId);

        $client = new AwsRoute53($fakeAwsRoute53Api);
        $ret = $client->getSingleRecord("recordName_3", $zoneId);

        $expected = null;
        $this->assertEquals($expected, $ret);
    }

    public function testMethodIsBlacklisted()
    {
        $args = ["name55"];
        $this->helper->dontCallSdkMethodInBlacklistedMethod("getSingleRecord", "listResourceRecordSets", $args);
    }
}
