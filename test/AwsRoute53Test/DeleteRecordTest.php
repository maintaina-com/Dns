<?php

namespace Horde\Dns\Test\AwsRoute53Test;

use Aws\Route53\Route53Client;
use Aws\Result as AwsResult;

use Horde\Dns\AwsRoute53;
use Horde\Dns\RecordPlain;
use Horde\Dns\Test\TestBase;

class DeleteRecordTest extends TestBase
{
    public function testDeletesCorrectRecord()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->generateRecordSets(20, $zoneId);
        $recordSets = $fakeAwsRoute53Api->getRecordSets($zoneId);

        $params = [
            "name" => "name43543222",
            "type" => "A",
            "ttl" => 600,
            "value" => "recordValue43543534",
        ];
        $recordSet = $fakeAwsRoute53Api->getRecordSet($params);
        $fakeAwsRoute53Api->insertRecordSet($zoneId, $recordSet);

        $client = new AwsRoute53($fakeAwsRoute53Api);
        $client->deleteRecord($zoneId, $params["name"], $params["type"], $params["value"], $params["ttl"]);
        $idx = $fakeAwsRoute53Api->getRecordSetIndex($zoneId, $recordSet);

        $this->assertEquals(-1, $idx);
        $this->assertEquals(20, count($recordSets));
    }

    public function testFailSilentlyIfRecordNotFound()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $zoneId = "testZoneId23";
        $fakeAwsRoute53Api->generateRecordSets(20, $zoneId);
        $params = [
            "name" => "name43543222",
            "type" => "A",
            "ttl" => 600,
            "value" => "recordValue43543534",
        ];
        $client = new AwsRoute53($fakeAwsRoute53Api);
        $client->deleteRecord($zoneId, $params["name"], $params["type"], $params["value"], $params["ttl"]);

        $apiCalls = $fakeAwsRoute53Api->stats["apiCalls"];
        $this->assertEquals(1, count($apiCalls));
    }


    public function testMethodIsBlacklisted()
    {
        $args = ["zoneId55", "name55", "type55"];
        $this->helper->dontCallSdkMethodInBlacklistedMethod("deleteRecord", "listResourceRecordSets", $args);
    }
}
