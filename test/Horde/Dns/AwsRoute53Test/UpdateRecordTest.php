<?php

namespace Horde\Dns\AwsRoute53Test;

use Aws\Route53\Route53Client;
use Aws\Result as AwsResult;

use Horde\Dns\AwsRoute53;
use Horde\Dns\RecordPlain;
use Horde\Dns\TestBase;

class UpdateRecordTest extends TestBase
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
        $client->updateRecord($zoneId, $params["name"], $params["type"], $params["value"], $params["ttl"]);

        $expected = $recordSet;
        $actual = $fakeAwsRoute53Api->getRecordSets($zoneId)[0];
        $this->assertEquals($expected, $actual);
    }

    public function testChangesValuesOfExistingRecord()
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
        
        $params["value"] = "recordValue5";
        $recordSet = $fakeAwsRoute53Api->getRecordSet($params);
        $client = new AwsRoute53($fakeAwsRoute53Api);
        $client->updateRecord($zoneId, $params["name"], $params["type"], $params["value"], $params["ttl"]);

        $expected = $recordSet;
        $actual = $fakeAwsRoute53Api->getRecordSets($zoneId)[0];
        $this->assertEquals($expected, $actual);
    }

    public function testDoesNotCreateIfValueIsNullAndRecordDoesNotExist()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->addZone($zoneId);
        $params = [
            "name" => "name43543534",
            "type" => "A",
            "ttl" => 600,
            "value" => null,
        ];
        $client = new AwsRoute53($fakeAwsRoute53Api);
        $client->updateRecord($zoneId, $params["name"], $params["type"], $params["value"], $params["ttl"]);

        $recordSets = $fakeAwsRoute53Api->getRecordSets($zoneId);
        $this->assertEquals([], $recordSets);
    }

    public function testUpdatesIfValueIsNullAndRecordDoesExist()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->addZone($zoneId);
        $params = [
            "name" => "name43543534",
            "type" => "A",
            "ttl" => 600,
            "value" => null,
        ];
        $recordSet = $fakeAwsRoute53Api->getRecordSet($params);
        $fakeAwsRoute53Api->insertRecordSet($zoneId, $recordSet);

        $client = new AwsRoute53($fakeAwsRoute53Api);
        $client->updateRecord($zoneId, $params["name"], $params["type"], $params["value"], $params["ttl"]);

        $apiCalls = $fakeAwsRoute53Api->stats["apiCalls"];
        $this->assertEquals(2, count($apiCalls));
        $this->assertEquals("changeResourceRecordSets", $apiCalls[1]["name"]);
    }


    public function testMethodIsBlacklisted()
    {
        $args = ["zoneId55", "name55", "type55", "value55"];
        $this->helper->dontCallSdkMethodInBlacklistedMethod("updateRecord", "listResourceRecordSets", $args);
    }
}
