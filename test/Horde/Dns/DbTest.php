<?php

namespace Horde\Dns;

use Horde\Dns\Db\ZoneRepo;
use Horde\Dns\Db\RecordRepo;
use Horde\Dns\Db\Record as DbRecord;

use \ForeachIterator;

class DbTest extends TestBase
{
    public function testGetDefaultZoneId()
    {
        $zoneRepo = $this->createMock(ZoneRepo::class);
        $recordRepo = $this->createMock(RecordRepo::class);

        $zoneId = "zoneId54334";
        $db = new Db($zoneRepo, $recordRepo, ["defaultZoneId" => $zoneId]);

        $actual = $db->getDefaultZoneId();

        $this->assertEquals($zoneId, $actual);
    }

    public function testSetDefaultZoneId()
    {
        $zoneRepo = $this->createMock(ZoneRepo::class);
        $recordRepo = $this->createMock(RecordRepo::class);

        $zoneId = "zoneId54334";
        $db = new Db($zoneRepo, $recordRepo);

        $db->setDefaultZoneId($zoneId);
        $actual = $db->getDefaultZoneId();

        $this->assertEquals($zoneId, $actual);
    }


    public function testGetZoneUsesDefaultZoneIdIfEmpty()
    {
        $defaultZoneId = "zoneId54334";
        $expected = new ZonePlain($defaultZoneId, "name");
        $zoneRepo = $this->createMock(ZoneRepo::class);
        $zoneRepo
            ->method("findOne")
            ->will(
                $this->returnCallback(function ($params) use ($defaultZoneId, $expected) {
                    if ($params["name"] === $defaultZoneId) {
                        return $expected;
                    }
                    return null;
                })
            );
        $recordRepo = $this->createMock(RecordRepo::class);

        $db = new Db($zoneRepo, $recordRepo, ["defaultZoneId" => $defaultZoneId]);
        $zone = $db->getZone();

        $this->assertEquals($expected, $zone);
    }

    public function testGetZoneReturnsNullIfZoneNotFound()
    {
        $zoneRepo = $this->createMock(ZoneRepo::class);
        $zoneRepo
            ->method("findOne")
            ->willReturn(null);
        $recordRepo = $this->createMock(RecordRepo::class);

        $db = new Db($zoneRepo, $recordRepo);
        $zone = $db->getZone();

        $this->assertEquals(null, $zone);
    }

    public function testGetZoneRecordsReturnsEmptyArrayIfNoneFound()
    {
        $zoneRepo = $this->createMock(ZoneRepo::class);
        $recordRepo = $this->createMock(RecordRepo::class);
        $recordRepo
            ->method("find")
            ->willReturn([]);

        $db = new Db($zoneRepo, $recordRepo);
        $records = $db->getZoneRecords();

        $this->assertEquals([], $records);
    }

    public function testGetZoneRecordsUsesDefaultZoneIdIfEmpty()
    {
        $defaultZoneId = "zoneId54334";
        $expected = [new RecordPlain("name", "type", 500, "value")];
        $zoneRepo = $this->createMock(ZoneRepo::class);
        $recordRepo = $this->createMock(RecordRepo::class);
        $recordRepo
            ->method("find")
            ->will(
                $this->returnCallback(function ($params) use ($defaultZoneId, $expected) {
                    if ($params["zone"] === $defaultZoneId) {
                        return $expected;
                    }
                    return [];
                })
            );

        $db = new Db($zoneRepo, $recordRepo, ["defaultZoneId" => $defaultZoneId]);
        $records = $db->getZoneRecords();

        $this->assertEquals($expected, $records);
    }

    public function testGetSingleRecordReturnsNullIfNotFound()
    {
        $zoneRepo = $this->createMock(ZoneRepo::class);
        $recordRepo = $this->createMock(RecordRepo::class);
        $recordRepo
            ->method("findOne")
            ->willReturn(null);

        $db = new Db($zoneRepo, $recordRepo);
        $record = $db->getSingleRecord("name");

        $this->assertEquals(null, $record);
    }

    public function testGetSingleRecordUsesDefaultZoneIdIfEmpty()
    {
        $defaultZoneId = "zoneId54334";
        $expected = new RecordPlain("name", "type", 500, "value");
        $zoneRepo = $this->createMock(ZoneRepo::class);
        $recordRepo = $this->createMock(RecordRepo::class);
        $recordRepo
            ->method("findOne")
            ->will(
                $this->returnCallback(function ($params) use ($defaultZoneId, $expected) {
                    if ($params["zone"] === $defaultZoneId) {
                        return $expected;
                    }
                    return null;
                })
            );

        $db = new Db($zoneRepo, $recordRepo, ["defaultZoneId" => $defaultZoneId]);
        $record = $db->getSingleRecord("name");

        $this->assertEquals($expected, $record);
    }


    public function testCreateRecordUsesCorrectQueryFormat()
    {
        $recordParams = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
            "recordValue_45435",
            500,  // TTL
            "recordComment",
        ];

        $zoneRepo = $this->createMock(ZoneRepo::class);

        $correctQuery = false;
        $recordRepo = $this->createMock(RecordRepo::class);
        $recordRepo
            ->method("create")
            ->will(
                $this->returnCallback(function ($params) use (&$correctQuery) {
                    if (
                        array_key_exists("zone", $params)
                        && array_key_exists("name", $params)
                        && array_key_exists("ttl", $params)
                        && array_key_exists("class", $params)
                        && array_key_exists("type", $params)
                        && array_key_exists("special", $params)
                        && array_key_exists("rdata", $params)
                        && array_key_exists("length", $params)
                    ) {
                        $correctQuery = true;
                    }
                })
            );

        $db = new Db($zoneRepo, $recordRepo);
        $db->createRecord(...$recordParams);

        $this->assertTrue($correctQuery);
    }

    public function testDeleteRecordDeletesRecordIfFound()
    {
        $recordParams = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
        ];

        $zoneRepo = $this->createMock(ZoneRepo::class);
        $record = $this->getMockBuilder(DbRecord::class)
            ->disableOriginalConstructor()
            ->getMock();
        $record->expects($this->once())->method("delete");
        $recordRepo = $this->createMock(RecordRepo::class);
        $recordRepo
            ->method("findOne")
            ->willReturn($record);

        $db = new Db($zoneRepo, $recordRepo);
        $db->deleteRecord(...$recordParams);
    }

    public function testUpdateRecordCreatesNewRecordIfNotFound()
    {
        $recordParams = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
            "recordValue_45435",
            500,  // TTL
            "recordComment",
        ];

        $zoneRepo = $this->createMock(ZoneRepo::class);

        $created = false;
        $recordRepo = $this->createMock(RecordRepo::class);
        $recordRepo
            ->method("findOne")
            ->willReturn(null);
        $recordRepo
            ->method("create")
            ->will(
                $this->returnCallback(function ($params) use (&$created) {
                    if (
                        array_key_exists("zone", $params)
                        && array_key_exists("name", $params)
                        && array_key_exists("ttl", $params)
                        && array_key_exists("class", $params)
                        && array_key_exists("type", $params)
                        && array_key_exists("special", $params)
                        && array_key_exists("rdata", $params)
                        && array_key_exists("length", $params)
                    ) {
                        $created = true;
                    }
                })
            );

        $db = new Db($zoneRepo, $recordRepo);
        $db->updateRecord(...$recordParams);

        $this->assertTrue($created);
    }

    public function testUpdateRecordDoesNotCreateNewRecordIfFound()
    {
        $recordParams = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
            "recordValue_45435",
            500,  // TTL
            "recordComment",
        ];

        $zoneRepo = $this->createMock(ZoneRepo::class);

        $created = false;
        $record = $this->getMockBuilder(DbRecord::class)
            ->disableOriginalConstructor()
            ->getMock();
        $record->method("save")->willReturn(null);
        $recordRepo = $this->createMock(RecordRepo::class);
        $recordRepo
            ->method("findOne")
            ->willReturn($record);
        $recordRepo
            ->method("create")
            ->will(
                $this->returnCallback(function ($params) use (&$created) {
                    if (
                        array_key_exists("zone", $params)
                        && array_key_exists("name", $params)
                        && array_key_exists("ttl", $params)
                        && array_key_exists("class", $params)
                        && array_key_exists("type", $params)
                        && array_key_exists("special", $params)
                        && array_key_exists("rdata", $params)
                        && array_key_exists("length", $params)
                    ) {
                        $created = true;
                    }
                })
            );

        $db = new Db($zoneRepo, $recordRepo);
        $db->updateRecord(...$recordParams);

        $this->assertFalse($created);
    }

    public function testGetZoneBlacklistedDoesNotCallRepo()
    {
        $params = [
            "zoneId_45435",
        ];

        $zoneRepo = $this->createMock(ZoneRepo::class);
        $zoneRepo->expects($this->never())->method($this->anything());
        $recordRepo = $this->createMock(RecordRepo::class);

        $db = new Db($zoneRepo, $recordRepo, ["blacklist" => ["getZone"]]);
        $db->getZone(...$params);
    }


    public function testGetZoneRecordsBlacklistedDoesNotCallRepo()
    {
        $params = [
            "zoneId_45435",
        ];

        $zoneRepo = $this->createMock(ZoneRepo::class);
        $recordRepo = $this->createMock(RecordRepo::class);
        $recordRepo->expects($this->never())->method($this->anything());

        $db = new Db($zoneRepo, $recordRepo, ["blacklist" => ["getZoneRecords"]]);
        $db->getZoneRecords(...$params);
    }


    public function testGetSingleRecordBlacklistedDoesNotCallRepo()
    {
        $params = [
            "recordName_45435",
            "zoneId_45435",
        ];

        $zoneRepo = $this->createMock(ZoneRepo::class);
        $recordRepo = $this->createMock(RecordRepo::class);
        $recordRepo->expects($this->never())->method($this->anything());

        $db = new Db($zoneRepo, $recordRepo, ["blacklist" => ["getSingleRecord"]]);
        $db->getSingleRecord(...$params);
    }

    public function testCreateRecordBlacklistedDoesNotCallRepo()
    {
        $params = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
            "recordValue_45435",
            500,  // TTL
            "recordComment",
        ];
        $zoneRepo = $this->createMock(ZoneRepo::class);
        $recordRepo = $this->createMock(RecordRepo::class);
        $recordRepo->expects($this->never())->method($this->anything());

        $db = new Db($zoneRepo, $recordRepo, ["blacklist" => ["createRecord"]]);
        $db->createRecord(...$params);
    }

    public function testDeleteRecordBlacklistedDoesNotCallRepo()
    {
        $params = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
        ];
        $zoneRepo = $this->createMock(ZoneRepo::class);
        $recordRepo = $this->createMock(RecordRepo::class);
        $recordRepo->expects($this->never())->method($this->anything());

        $db = new Db($zoneRepo, $recordRepo, ["blacklist" => ["deleteRecord"]]);
        $db->deleteRecord(...$params);
    }

    public function testUpdateRecordBlacklistedDoesNotCallRepo()
    {
        $params = [
            "zoneId_45435",
            "recordName_45435",
            "recordType_45435",
            "recordValue_45435",
            500,  // TTL
            "recordComment",
        ];
        $zoneRepo = $this->createMock(ZoneRepo::class);
        $recordRepo = $this->createMock(RecordRepo::class);
        $recordRepo->expects($this->never())->method($this->anything());

        $db = new Db($zoneRepo, $recordRepo, ["blacklist" => ["updateRecord"]]);
        $db->updateRecord(...$params);
    }
}
