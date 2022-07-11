<?php

namespace Horde\Dns\Test;

use PHPUnit\Framework\TestCase;

class TestBase extends TestCase
{
    public function __construct()
    {
        parent::__construct();
        $thisClassPath = explode("\\", get_class($this));
        array_pop($thisClassPath);
        $helperClassPath = implode("\\", $thisClassPath) . "\\Helper";
        if (class_exists($helperClassPath)) {
            $this->helper = new $helperClassPath();
        } else {
            $this->helper = new Helper();
        }
    }
}
