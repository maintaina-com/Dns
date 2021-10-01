<?php

namespace Horde\Dns\AwsRoute53Test;

use Aws\Route53\Route53Client;
use Aws\Result as AwsResult;

use Horde\Dns\AwsRoute53;
use Horde\Dns\TestBase;

class BasicTest extends TestBase
{
    public function testGetDefaultZoneId()
    {
        $testDefaultZoneId = "testZoneId553";
        [$sdkMock, $client] = $this->helper->createSdkMockAndClient(["defaultZoneId" => $testDefaultZoneId]);
        $actual = $client->getDefaultZoneId();
        $expected = $testDefaultZoneId;

        $this->assertEquals($expected, $actual);
    }

    public function testSetDefaultZoneId()
    {
        [$sdkMock, $client] = $this->helper->createSdkMockAndClient(["defaultZoneId" => "testZoneId5564564"]);
        
        $testDefaultZoneId = "testZoneId554";
        $client->setDefaultZoneId($testDefaultZoneId);
        $actual = $client->getDefaultZoneId();
        $expected = $testDefaultZoneId;

        $this->assertEquals($expected, $actual);
    }

    public function testGetZoneBlacklisted()
    {
        $this->helper->dontCallSdkMethodInBlacklistedMethod("getZone", "getHostedZone");
    }
}
