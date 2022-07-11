<?php

namespace Horde\Dns\Test\Db;

use Horde\Dns\Test\TestBase;

use Horde\Dns\Db\ZoneRepo;

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
