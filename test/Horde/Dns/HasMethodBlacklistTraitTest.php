<?php

namespace Horde\Dns;

class HasMethodBlacklistTraitTest extends TestBase
{
    public function testSetMethodBlacklist()
    {
        $mock = $this->getMockBuilder(HasMethodBlacklistTrait::class)
            ->getMockForTrait();
        $blacklist = ["a", "b", "c"];
        $this->helper->invokeMethod($mock, "setMethodBlacklist", [$blacklist]);
        foreach ($blacklist as $entry) {
            $this->assertTrue(
                $this->helper->invokeMethod($mock, "methodIsBlacklisted", [$entry])
            );
        }
    }
}
