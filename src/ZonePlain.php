<?php
namespace Horde\Dns;

/**
 * A simple DNS Zone representation
 */
class ZonePlain implements Zone
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $comment;

    public function __construct(string $id, string $name, string $comment = '')
    {
        $this->id = $id;
        $this->name = $name;
        $this->comment = $comment;
    }

    /**
     * A comment on a zone
     *
     * @return string The Comment
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * A zone's root dns name
     *
     * @return string The Comment
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * A Zone's unique ID in the DNS backend
     *
     * @return string The ID
     */
    public function getId(): string
    {
        return $this->id;
    }
}
