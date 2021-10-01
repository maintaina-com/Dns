<?php

namespace Horde\Dns\Db;

use Horde\Dns\TestBase;

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
