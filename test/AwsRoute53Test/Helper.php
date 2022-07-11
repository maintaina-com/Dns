<?php

namespace Horde\Dns\Test\AwsRoute53Test;

use Aws\Route53\Route53Client;
use Aws\Result as AwsResult;

use Horde\Dns\AwsRoute53;
use Horde\Dns\Test\Helper as BaseHelper;

class Helper extends BaseHelper
{
    public function getSdkMockBuilder($methods = [])
    {
        $sdkBuilder = $this
            ->getMockBuilder(Route53Client::class)
            ->disableOriginalConstructor();
        if (!empty($methods)) {
            $sdkBuilder->setMethods($methods);
        }
        return $sdkBuilder;
    }

    public function createSdkMockAndClient($parameters = [])
    {
        $sdkMock = $this->createMock(Route53Client::class);
        $client = new AwsRoute53($sdkMock, $parameters);
        return [$sdkMock, $client];
    }

    public function dontCallSdkMethodInBlacklistedMethod(
        $blacklistedMethod,
        $sdkMethod,
        $blacklistedMethodArgs = [],
        $expected = null
    ) {
        $sdk = $this->getSdkMockBuilder([$sdkMethod])->getMock();
        $sdk->expects($this->never())->method($sdkMethod);

        $client = new AwsRoute53(
            $sdk,
            ["blacklist" => [$blacklistedMethod]]
        );

        $actual = $client->$blacklistedMethod(...$blacklistedMethodArgs);

        $this->assertEquals($expected, $actual);
    }
}
