<?php

namespace Horde\Dns;

use \ForeachIterator;

class CompositeTest extends TestBase
{
    public function testGetDefaultZoneId()
    {
        $client = $this->createMock(Client::class);
        $clients = [$client];
        $zoneId = "zoneId54334";
        $comp = new Composite($clients, ["defaultZoneId" => $zoneId]);

        $actual = $comp->getDefaultZoneId();

        $this->assertEquals($zoneId, $actual);
    }

    public function testSetDefaultZoneId()
    {
        $client = $this->createMock(Client::class);
        $clients = [$client];
        $zoneId = "zoneId54334";
        $comp = new Composite($clients);

        $comp->setDefaultZoneId($zoneId);
        $actual = $comp->getDefaultZoneId();

        $this->assertEquals($zoneId, $actual);
    }

    public function testGetZoneGetsZoneFromFirstMatchingClient()
    {
        $clients = [];
        for ($i = 0; $i <= 10; $i++) {
            $client = $this->createMock(Client::class);
            if ($i % 2 > 0) {
                $zone = $this->createMock(Zone::class);
                $zone->method('getId')
                ->willReturn("".$i);
                $client->method('getZone')
                ->willReturn($zone);
            } else {
                $client->method('getZone')
                ->willReturn(null);
            }
            $clients[] = $client;
        }
        $comp = new Composite($clients);

        $zone = $comp->getZone("5");
        $z_id = $zone->getId();
        $expected = "1";
        $this->assertEquals($expected, $z_id);
    }

    public function testGetZoneReturnsNullWhenZoneNotInClients()
    {
        $clients = [];
        for ($i = 0; $i <= 10; $i++) {
            $client = $this->createMock(Client::class);
            $client->method('getZone')
                ->willReturn(null);
            $clients[] = $client;
        }
        $comp = new Composite($clients);

        $zone = $comp->getZone("5");

        $expected = null;
        $this->assertEquals($expected, $zone);
    }

    public function testGetZoneUsesDefaultZoneIdIfEmpty()
    {
        $defaultZoneId = "zoneId34543";
        $client = $this->createMock(Client::class);
        $client->method('getZone')
            ->will(
                $this->returnCallback(function ($zoneId) use ($defaultZoneId) {
                    if ($defaultZoneId === $zoneId) {
                        return new ZonePlain($zoneId, "zoneName");
                    }
                    return null;
                })
            );
        $clients = [$client];

        $comp = new Composite($clients, ["defaultZoneId" => $defaultZoneId]);
        $zone = $comp->getZone();

        $expected = new ZonePlain($defaultZoneId, "zoneName");
        $this->assertEquals($expected, $zone);
    }

    public function testGetZoneRecordsReturnsEmptyArrayIfNoRecordsFound()
    {
        $client = $this->createMock(Client::class);
        $client
            ->method('getZoneRecords')
            ->willReturn([]);
        $clients = [$client];

        $comp = new Composite($clients);
        $res = $comp->getZoneRecords();

        $this->assertEquals([], $res);
    }

    public function testGetZoneRecordsUsesDefaultZoneIdIfEmpty()
    {
        $defaultZoneId = "zoneId34543";
        $client = $this->createMock(Client::class);
        $result = [new RecordPlain("recordName", "recordType", 600, 20)];
        $client->method('getZoneRecords')
            ->will(
                $this->returnCallback(function ($zoneId, $filters, $maxResults) use ($defaultZoneId, $result) {
                    if ($defaultZoneId === $zoneId) {
                        return $result;
                    }
                    return [];
                })
            );
        $clients = [$client];

        $comp = new Composite($clients, ["defaultZoneId" => $defaultZoneId]);
        $zoneRecords = $comp->getZoneRecords();

        $this->assertEquals($result, $zoneRecords);
    }

    public function testGetSingleRecordUsesDefaultZoneIdIfEmpty()
    {
        $zoneRecordName = "recordName543";
        $result = new RecordPlain($zoneRecordName, "recordType", 600, 20);
        $defaultZoneId = "zoneId34543";
        $client = $this->createMock(Client::class);
        $client
            ->method('getSingleRecord')
            ->will(
                $this->returnCallback(function ($name, $zoneId, $filters) use ($defaultZoneId, $result) {
                    if ($defaultZoneId === $zoneId) {
                        return $result;
                    }
                    return [];
                })
            );
        $clients = [$client];

        $comp = new Composite($clients, ["defaultZoneId" => $defaultZoneId]);
        $zoneRecord = $comp->getSingleRecord($zoneRecordName);

        $this->assertEquals($result, $zoneRecord);
    }

    public function testGetSingleRecordCatchesExceptionFromClients()
    {
        $zoneRecordName = "recordName543";
        $result = new RecordPlain($zoneRecordName, "recordType", 600, 20);

        $clients = [];
        $client = $this->createMock(Client::class);
        $client
            ->method('getSingleRecord')
            ->will(
                $this->throwException(new \Exception())
            );
        $clients[] = $client;
        $client = $this->createMock(Client::class);
        $client
            ->method('getSingleRecord')
            ->willReturn($result);
        $clients[] = $client;

        $comp = new Composite($clients);
        $zoneRecord = $comp->getSingleRecord($zoneRecordName);

        $this->assertEquals($result, $zoneRecord);
    }

    public function testGetSingleRecordReturnsFirstNonNullClientResponse()
    {
        $zoneRecordName = "recordName543";
        $result = new RecordPlain($zoneRecordName, "recordType", 600, 20);

        $clients = [];
        for ($i = 0; $i <= 10; $i++) {
            $client = $this->createMock(Client::class);
            $client
                ->method('getSingleRecord')
                ->willReturn(null);
            $clients[] = $client;
        }
        $client = $this->createMock(Client::class);
        $client
            ->method('getSingleRecord')
            ->willReturn($result);
        $clients[] = $client;

        $comp = new Composite($clients);
        $zoneRecord = $comp->getSingleRecord($zoneRecordName);

        $this->assertEquals($result, $zoneRecord);
    }

    public function testGetSingleRecordReturnsNullIfNotFoundInClients()
    {
        $zoneRecordName = "recordName543";
        $clients = [];
        for ($i = 0; $i <= 10; $i++) {
            $client = $this->createMock(Client::class);
            $client
                ->method('getSingleRecord')
                ->willReturn(null);
            $clients[] = $client;
        }

        $comp = new Composite($clients);
        $zoneRecord = $comp->getSingleRecord($zoneRecordName);

        $this->assertEquals(null, $zoneRecord);
    }


    public function testCreateRecordCallsMethodOnAllClients()
    {
        $params = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
            "recordValue_45435",
            500,  // TTL
        ];

        $num = 10;
        $calledClients = [];
        $clients = [];
        for ($i = 0; $i < $num; $i++) {
            $calledClients[] = false;
            $client = $this->createMock(Client::class);
            $client
                ->method('createRecord')
                ->will(
                    $this->returnCallback(function (...$args) use (&$calledClients, $i) {
                        $calledClients[$i] = true;
                    })
                );
            $clients[] = $client;
        }

        $comp = new Composite($clients);
        $zoneRecord = $comp->createRecord(...$params);

        $this->assertNotContains(false, $calledClients);
    }

    public function testCreateRecordCatchesExceptionFromClients()
    {
        $params = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
            "recordValue_45435",
            500,  // TTL
        ];

        $num = 10;
        $reachedClient = 0;
        $clients = [];
        for ($i = 0; $i < $num; $i++) {
            $calledClients[] = false;
            $client = $this->createMock(Client::class);
            if ($i < 5) {
                $client
                    ->method('createRecord')
                    ->will(
                        $this->throwException(new \Exception())
                    );
            } else {
                $client
                    ->method('createRecord')
                    ->will(
                        $this->returnCallback(function (...$args) use (&$reachedClient, $i) {
                            $reachedClient = $i;
                        })
                    );
            }
            $clients[] = $client;
        }

        $comp = new Composite($clients);
        $zoneRecord = $comp->createRecord(...$params);

        $this->assertEquals($num-1, $reachedClient);
    }

    public function testCreateRecordPassesArgumentsUnchanged()
    {
        $params = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
            "recordValue_45435",
            500,  // TTL
            "recordComment",
        ];
        $passedSameArgs = false;
        $client = $this->createMock(Client::class);
        $client
            ->method('createRecord')
            ->will(
                $this->returnCallback(function (...$args) use (&$passedSameArgs, $params) {
                    if ($args === $params) {
                        $passedSameArgs = true;
                    }
                })
            );
        $clients = [$client];
        $comp = new Composite($clients);
        $zoneRecord = $comp->createRecord(...$params);
        $this->assertTrue($passedSameArgs);
    }


    public function testDeleteRecordCallsMethodOnAllClients()
    {
        $params = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
        ];

        $num = 10;
        $calledClients = [];
        $clients = [];
        for ($i = 0; $i < $num; $i++) {
            $calledClients[] = false;
            $client = $this->createMock(Client::class);
            $client
                ->method('deleteRecord')
                ->will(
                    $this->returnCallback(function (...$args) use (&$calledClients, $i) {
                        $calledClients[$i] = true;
                    })
                );
            $clients[] = $client;
        }

        $comp = new Composite($clients);
        $zoneRecord = $comp->deleteRecord(...$params);

        $this->assertNotContains(false, $calledClients);
    }

    public function testDeleteRecordCatchesExceptionsFromClients()
    {
        $params = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
        ];

        $num = 10;
        $reachedClient = 0;
        $clients = [];
        for ($i = 0; $i < $num; $i++) {
            $calledClients[] = false;
            $client = $this->createMock(Client::class);
            if ($i < 5) {
                $client
                    ->method('deleteRecord')
                    ->will(
                        $this->throwException(new \Exception())
                    );
            } else {
                $client
                    ->method('deleteRecord')
                    ->will(
                        $this->returnCallback(function (...$args) use (&$reachedClient, $i) {
                            $reachedClient = $i;
                        })
                    );
            }
            $clients[] = $client;
        }

        $comp = new Composite($clients);
        $zoneRecord = $comp->deleteRecord(...$params);

        $this->assertEquals($num-1, $reachedClient);
    }

    public function testDeleteRecordPassesArgumentsUnchanged()
    {
        $params = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
            "recordComment",
        ];
        $passedSameArgs = false;
        $client = $this->createMock(Client::class);
        $client
            ->method('deleteRecord')
            ->will(
                $this->returnCallback(function (...$args) use (&$passedSameArgs, $params) {
                    if ($args === $params) {
                        $passedSameArgs = true;
                    }
                })
            );
        $clients = [$client];
        $comp = new Composite($clients);
        $zoneRecord = $comp->deleteRecord(...$params);
        $this->assertTrue($passedSameArgs);
    }


    public function testUpdateRecordCallsMethodOnAllClients()
    {
        $params = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
            "recordValue_45435",
            500,  // TTL
        ];

        $num = 10;
        $calledClients = [];
        $clients = [];
        for ($i = 0; $i < $num; $i++) {
            $calledClients[] = false;
            $client = $this->createMock(Client::class);
            $client
                ->method('updateRecord')
                ->will(
                    $this->returnCallback(function (...$args) use (&$calledClients, $i) {
                        $calledClients[$i] = true;
                    })
                );
            $clients[] = $client;
        }

        $comp = new Composite($clients);
        $zoneRecord = $comp->updateRecord(...$params);

        $this->assertNotContains(false, $calledClients);
    }

    public function testUpdateRecordCatchesExceptionsFromClients()
    {
        $params = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
            "recordValue_45435",
            500,  // TTL
        ];

        $num = 10;
        $reachedClient = 0;
        $clients = [];
        for ($i = 0; $i < $num; $i++) {
            $calledClients[] = false;
            $client = $this->createMock(Client::class);
            if ($i < 5) {
                $client
                    ->method('updateRecord')
                    ->will(
                        $this->throwException(new \Exception())
                    );
            } else {
                $client
                    ->method('updateRecord')
                    ->will(
                        $this->returnCallback(function (...$args) use (&$reachedClient, $i) {
                            $reachedClient = $i;
                        })
                    );
            }
            $clients[] = $client;
        }

        $comp = new Composite($clients);
        $zoneRecord = $comp->updateRecord(...$params);

        $this->assertEquals($num-1, $reachedClient);
    }

    public function testUpdateRecordPassesArgumentsUnchanged()
    {
        $params = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
            "recordValue_45435",
            500,  // TTL
            "recordComment",
        ];
        $passedSameArgs = false;
        $client = $this->createMock(Client::class);
        $client
            ->method('updateRecord')
            ->will(
                $this->returnCallback(function (...$args) use (&$passedSameArgs, $params) {
                    if ($args === $params) {
                        $passedSameArgs = true;
                    }
                })
            );
        $clients = [$client];
        $comp = new Composite($clients);
        $zoneRecord = $comp->updateRecord(...$params);
        $this->assertTrue($passedSameArgs);
    }

    public function testGetZoneBlacklistedDoesNotCallClients()
    {
        $params = [
            "zoneId_45435",
        ];
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method("getZone");
        $clients = [$client];
        $comp = new Composite($clients, ["blacklist" => ["getZone"]]);
        $zoneRecord = $comp->getZone(...$params);
    }


    public function testGetZoneRecordsBlacklistedDoesNotCallClients()
    {
        $params = [
            "zoneId_45435",
        ];
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method("getZoneRecords");
        $clients = [$client];
        $comp = new Composite($clients, ["blacklist" => ["getZoneRecords"]]);
        $zoneRecord = $comp->getZoneRecords(...$params);
    }


    public function testGetSingleRecordBlacklistedDoesNotCallClients()
    {
        $params = [
            "recordName_45435",
            "zoneId_45435",
        ];
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method("getSingleRecord");
        $clients = [$client];
        $comp = new Composite($clients, ["blacklist" => ["getSingleRecord"]]);
        $zoneRecord = $comp->getSingleRecord(...$params);
    }

    public function testCreateRecordBlacklistedDoesNotCallClients()
    {
        $params = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
            "recordValue_45435",
            500,  // TTL
            "recordComment",
        ];
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method("createRecord");
        $clients = [$client];
        $comp = new Composite($clients, ["blacklist" => ["createRecord"]]);
        $zoneRecord = $comp->createRecord(...$params);
    }

    public function testDeleteRecordBlacklistedDoesNotCallClients()
    {
        $params = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
        ];
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method("deleteRecord");
        $clients = [$client];
        $comp = new Composite($clients, ["blacklist" => ["deleteRecord"]]);
        $zoneRecord = $comp->deleteRecord(...$params);
    }

    public function testUpdateRecordBlacklistedDoesNotCallClients()
    {
        $params = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
            "recordValue_45435",
            500,  // TTL
            "recordComment",
        ];
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method("updateRecord");
        $clients = [$client];
        $comp = new Composite($clients, ["blacklist" => ["updateRecord"]]);
        $zoneRecord = $comp->updateRecord(...$params);
    }
}
