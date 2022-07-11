<?php

namespace Horde\Dns\Test;

use Horde\Dns\ZonePlain;
use Horde\Dns\RecordPlain;

class ZonePlainTest extends TestBase
{
    private function getZone($updates)
    {
        $params = [
            "id" => "zoneId",
            "name" => "zoneName",
            "comment" => "zoneComment",
        ];
        $updated = array_merge($params, $updates);
        return new ZonePlain(
            $updated["id"],
            $updated["name"],
            $updated["comment"]
        );
    }
    public function testGetName()
    {
        $expected = "test23423423";
        $zone = $this->getZone(["name" => $expected]);
        $actual = $zone->getName();

        $this->assertEquals($expected, $actual);
    }

    public function testGetId()
    {
        $expected = "test8673423";
        $zone = $this->getZone(["id" => $expected]);
        $actual = $zone->getId();

        $this->assertEquals($expected, $actual);
    }

    public function testGetComment()
    {
        $expected = "test7676856";
        $zone = $this->getZone(["comment" => $expected]);
        $actual = $zone->getComment();

        $this->assertEquals($expected, $actual);
    }
}
