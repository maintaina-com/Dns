<?php
namespace Horde\Dns;

interface Zone
{
    /**
     * A comment on a zone
     *
     * @return string The Comment
     */
    public function getComment(): string;

    /**
     * A zone's root dns name
     *
     * @return string The Comment
     */
    public function getName(): string;

    /**
     * A Zone's unique ID in the DNS backend
     *
     * @return string The ID
     */
    public function getId(): string;
}
