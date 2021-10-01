<?php

namespace Horde\Dns\Db;

use Horde\Dns\Zone as ZoneInterface;
use Horde\Dns\Record as RecordInterface;

class RecordRepo extends \Horde_Rdo_Mapper
{
    protected $_classname = 'Horde\Dns\Db\Record';
    protected $_table = 'horde_dns_records';
}
