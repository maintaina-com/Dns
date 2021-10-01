<?php

namespace Horde\Dns\AwsRoute53Test;

use Aws\Exception\AwsException as AwsE;
use Aws\Command;

class AwsException extends AwsE
{
    public function __construct(string $message)
    {
        $command = new Command("genericCommandName");
        parent::__construct($message, $command);
    }
}
