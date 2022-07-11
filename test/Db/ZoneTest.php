<?php

namespace Horde\Dns\Test\Db;

use Horde\Dns\Test\TestBase;

use Horde\Dns\Db\Zone;

class ZoneTest extends TestBase
{
    private function getZone($updates)
    {
        // this is a bit complicated because in Horde\Dns\Db\Zone the 'id' maps to 'name' and the 'name' maps to 'domain'
        $params = [
            "name" => "zoneId",
            "domain" => "zoneName",
            "comment" => "zoneComment",
        ];
        $updated = array_merge($params, $updates);
        return new Zone($updated);
    }

    public function testGetName()
    {
        $expected = "test23423423";
        $zone = $this->getZone(["domain" => $expected]);
        $actual = $zone->getName();

        $this->assertEquals($expected, $actual);
    }

    public function testGetId()
    {
        $expected = "test8673423";
        $zone = $this->getZone(["name" => $expected]);
        $actual = $zone->getId();

        $this->assertEquals($expected, $actual);
    }

    public function testGetComment()
    {
        $expected = "";
        $zone = $this->getZone(["comment" => $expected]);
        $actual = $zone->getComment();

        $this->assertEquals($expected, $actual);
    }
}
