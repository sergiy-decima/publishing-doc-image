<?php

namespace Decima\Doc\Test;

use PHPUnit\Framework\TestCase;

class HelloTest extends TestCase
{
    public function testHi()
    {
        $math = new \Decima\Doc\Hello();
        $this->assertEquals('hi', $math->hello());
    }
}
