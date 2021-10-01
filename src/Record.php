<?php
namespace Horde\Dns;

interface Record
{
    /**
     * The record name
     *
     * @return string The record's name
     */
    public function getName(): string;

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
    public function getType(): string;

    /**
     * The TTL value
     *
     * Governs how long a record may be cached by clients or downstream DNS servers
     *
     * @return int The TTL
     */
    public function getTtl(): int;

    /**
     * The record value
     *
     * Depending on record type, this is a string or list of strings
     *
     * @return string|string[] The comment
     */
    public function getValue();

    /**
     * A comment on the record
     *
     * Backends which do not implement a comment option should return an empty string
     *
     * @return string The comment
     */
    public function getComment(): string;
}
