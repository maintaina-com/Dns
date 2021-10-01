<?php
namespace Horde\Dns\Db;

use Horde\Dns\Zone as ZoneInterface;
use Horde\Dns\Record as RecordInterface;

class Zone extends \Horde_Rdo_Base implements ZoneInterface
{
    protected $_mapper = 'Horde\Dns\Db\ZoneRepo';
    /**
     * A comment on a zone
     *
     * @return string The Comment
     */
    public function getComment(): string
    {
        return '';
    }

    /**
     * A zone's root dns name
     *
     * @return string The Comment
     */
    public function getName(): string
    {
        return $this->_fields['domain'];
    }

    /**
     * A Zone's unique ID in the DNS backend
     *
     * @return string The ID
     */
    public function getId(): string
    {
        return $this->_fields['name'];
    }
}
