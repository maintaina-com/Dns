<?php

namespace Horde\Dns\AwsRoute53Test;

use Aws\Route53\Route53Client;
use Aws\Result as AwsResult;

use Horde\Dns\AwsRoute53;
use Horde\Dns\TestBase;

class GetZoneTest extends TestBase
{
    public function testMethodIsBlacklisted()
    {
        $this->helper->dontCallSdkMethodInBlacklistedMethod("getZone", "getHostedZone");
    }

    public function testUseDefaultZoneIdWhenEmpty()
    {
        $defaultZoneId = "zoneID8787";
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $fakeAwsRoute53Api->addZone($defaultZoneId);

        $client = new AwsRoute53(
            $fakeAwsRoute53Api,
            ["defaultZoneId" => $defaultZoneId]
        );
        $ret = $client->getZone();
        $actual = $ret->getId();
        $expected = $defaultZoneId;
        $this->assertEquals($expected, $actual);
    }

    public function testZoneNotFound()
    {
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $fakeAwsRoute53Api->addZone("zoneId325");

        $client = new AwsRoute53($fakeAwsRoute53Api);
        $ret = $client->getZone("testZoneNotFound");

        $this->assertEquals(null, $ret);
    }

    public function testHandleZonePrefix()
    {
        $prefix = "/hostedzone/";
        $withoutPrefix = "zoneID7567345";
        $zoneId = $prefix . $withoutPrefix;
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $fakeAwsRoute53Api->addZone($zoneId);

        $client = new AwsRoute53($fakeAwsRoute53Api);
        $ret = $client->getZone($zoneId);
        $actual = $ret->getId();
        $expected = $withoutPrefix;
        $this->assertEquals($expected, $actual);
    }

    public function testHandleTrailingDotsInZoneName()
    {
        $zoneId = "zoneId52532";
        $nameWithoutDots = "name7567345";
        $name = $nameWithoutDots . "...";
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $fakeAwsRoute53Api->addZone($zoneId, ["Name" => $name]);

        $client = new AwsRoute53($fakeAwsRoute53Api);
        $ret = $client->getZone($zoneId);
        $actual = $ret->getName();
        $expected = $nameWithoutDots;
        $this->assertEquals($expected, $actual);
    }

    public function testReturnsZoneInstance()
    {
        $zoneId = "zoneID8787";
        $fakeAwsRoute53Api = new FakeAwsRoute53Api();
        $fakeAwsRoute53Api->addZone($zoneId);

        $client = new AwsRoute53($fakeAwsRoute53Api);
        $zone = $client->getZone($zoneId);
        $interfaces = class_implements($zone);
        $this->assertEquals(true, in_array("Horde\Dns\Zone", $interfaces));
    }
}
