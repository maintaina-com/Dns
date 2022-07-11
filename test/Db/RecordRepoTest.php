<?php

namespace Horde\Dns\Test\Db;

use Horde\Dns\Test\TestBase;

use Horde\Dns\Db\RecordRepo;

class RecordRepoTest extends TestBase
{
    public function testRecordHasClassnameAttribute()
    {
        $this->assertClassHasAttribute('_classname', RecordRepo::class);
    }

    public function testRecordHasTableAttribute()
    {
        $this->assertClassHasAttribute('_table', RecordRepo::class);
    }
}
