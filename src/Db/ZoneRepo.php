<?php
namespace Horde\Dns\Db;

use Horde\Dns\Zone as ZoneInterface;
use Horde\Dns\Record as RecordInterface;

class ZoneRepo extends \Horde_Rdo_Mapper
{
    protected $_table = 'horde_dns_zones';
    protected $_classname = 'Horde\Dns\Db\Zone';
}
