<?php

namespace Decima\Doc\Test;

use PHPUnit\Framework\TestCase;
class MathTest extends TestCase
{
    public function testSum()
    {
        $math = new \Decima\Doc\Math();
        $this->assertEquals(6, $math->sum(2,3));
    }

    public function testHi()
    {
        $math = new \Decima\Doc\Math();
        $this->assertEquals('hi', $math->hello());
    }
}
