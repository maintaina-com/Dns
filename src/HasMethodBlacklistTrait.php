<?php

namespace Horde\Dns;

/**
 * Trait to add a method blacklist to a class
 * Initiation and enforcement of the blacklist still has to be done in the using class
 */
trait HasMethodBlacklistTrait
{
    /** @var string[] */
    private $methodBlacklist = [];

    private function setMethodBlacklist(?array $methods)
    {
        if (is_null($methods)) {
            $methods = [];
        }
        $methodBlacklist = [];
        foreach ($methods as $method) {
            if (!in_array($method, $methodBlacklist)) {
                $methodBlacklist[] = $method;
            }
        }
        $this->methodBlacklist = $methodBlacklist;
    }

    private function methodIsBlacklisted(string $methodName): bool
    {
        if (in_array($methodName, $this->methodBlacklist)) {
            return true;
        } else {
            return false;
        }
    }
}
