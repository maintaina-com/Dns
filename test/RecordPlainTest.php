<?php

namespace Horde\Dns\Test;

use Horde\Dns\ZonePlain;
use Horde\Dns\RecordPlain;

class RecordPlainTest extends TestBase
{
    private function getRecord($updates)
    {
        $params = [
            "name" => "recordName",
            "type" => "recordType",
            "ttl" => 600,
            "value" => "recordValue",
            "comment" => "recordComment",
        ];
        $updated = array_merge($params, $updates);
        return new RecordPlain(
            $updated["name"],
            $updated["type"],
            $updated["ttl"],
            $updated["value"],
            $updated["comment"]
        );
    }
    public function testGetName()
    {
        $expected = "test23423423";
        $record = $this->getRecord(["name" => $expected]);
        $actual = $record->getName();

        $this->assertEquals($expected, $actual);
    }

    public function testGetType()
    {
        $expected = "test423342343";
        $record = $this->getRecord(["type" => $expected]);

        $actual = $record->getType();

        $this->assertEquals($expected, $actual);
    }

    public function testGetTtl()
    {
        $expected = 434;
        $record = $this->getRecord(["ttl" => $expected]);
        $actual = $record->getTtl();

        $this->assertEquals($expected, $actual);
    }

    public function testGetValue()
    {
        $expected = "test54654353";
        $record = $this->getRecord(["value" => $expected]);
        $actual = $record->getValue();

        $this->assertEquals($expected, $actual);
    }

    public function testGetComment()
    {
        $expected = "test7676856";
        $record = $this->getRecord(["comment" => $expected]);
        $actual = $record->getComment();

        $this->assertEquals($expected, $actual);
    }
}
