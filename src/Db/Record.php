<?php

namespace Horde\Dns\Db;

use Horde\Dns\Zone as ZoneInterface;
use Horde\Dns\Record as RecordInterface;
use \Horde_Rdo_Base;

class Record extends \Horde_Rdo_Base implements RecordInterface
{
    protected $_mapper = 'Horde\Dns\Db\RecordRepo';
    /**
     * The record name
     *
     * @return string The record's name
     */
    public function getName(): string
    {
        return $this->_fields['name'];
    }

    /**
     * The record type
     *
     * Most prominent types are:
     *  A     IPv4 host records, hostname to IP
     *  AAAA  IPv6 host records, hostname to IP
     *  CNAME A lookup from one hostname to another hostname
     *  TXT   Data records, for example anti spam or verification
     *  SRV   Service lookup
     *  MX    Mail handling delegation (righthand handles SMTP for lefthand)
     *  NS    Point to a related name server
     *
     * @return string Type Designation
     */
    public function getType(): string
    {
        return $this->_fields['type'];
    }

    /**
     * The TTL value
     *
     * Governs how long a record may be cached by clients or downstream DNS servers
     *
     * @return int The TTL
     */
    public function getTtl(): int
    {
        return (int) $this->_fields['ttl'];
    }

    /**
     * The record value
     *
     * Depending on record type, this is a string or list of strings
     *
     * @return string|string[] The comment
     */
    public function getValue()
    {
        return $this->_fields['rdata'];
    }

    /**
     * A comment on the record
     *
     * Backends which do not implement a comment option should return an empty string
     *
     * @return string The comment
     */
    public function getComment(): string
    {
        return '';
    }
}
