<?php

namespace Horde\Dns\AwsRoute53Test;

use Aws\Route53\Route53Client;
use Aws\Result as AwsResult;

use Horde\Dns\AwsRoute53;
use Horde\Dns\RecordPlain;
use Horde\Dns\TestBase;

class CreateRecordTest extends TestBase
{
    public function testCreatesRecordWithCorrectVars()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->addZone($zoneId);
        $params = [
            "name" => "name43543534",
            "type" => "A",
            "ttl" => 600,
            "value" => "recordValue43543534",
        ];
        $recordSet = $fakeAwsRoute53Api->getRecordSet($params);

        $client = new AwsRoute53($fakeAwsRoute53Api);
        $client->createRecord($zoneId, $params["name"], $params["type"], $params["value"], $params["ttl"]);

        $expected = $recordSet;
        $actual = $fakeAwsRoute53Api->getRecordSets($zoneId)[0];
        $this->assertEquals($expected, $actual);
    }

    public function testDoesntChangeValuesOfExistingRecord()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->addZone($zoneId);
        $params = [
            "name" => "name43543534",
            "type" => "A",
            "ttl" => 600,
            "value" => "recordValue1",
        ];
        $recordSet = $fakeAwsRoute53Api->getRecordSet($params);
        $fakeAwsRoute53Api->insertRecordSet($zoneId, $recordSet);
        $client = new AwsRoute53($fakeAwsRoute53Api);
        $client->createRecord($zoneId, $params["name"], $params["type"], "recordValue5", $params["ttl"]);

        $expected = $recordSet;
        $actual = $fakeAwsRoute53Api->getRecordSets($zoneId)[0];
        $this->assertEquals($expected, $actual);
    }

    public function testMethodIsBlacklisted()
    {
        $args = ["zoneId55", "name55", "type55", "value55"];
        $this->helper->dontCallSdkMethodInBlacklistedMethod("createRecord", "changeResourceRecordSets", $args);
    }
}
