<?php

namespace Horde\Dns\Db;

use Horde\Dns\TestBase;

class ZoneRepoTest extends TestBase
{
    public function testZoneHasClassnameAttribute()
    {
        $this->assertClassHasAttribute('_classname', ZoneRepo::class);
    }

    public function testZoneHasTableAttribute()
    {
        $this->assertClassHasAttribute('_table', ZoneRepo::class);
    }
}
