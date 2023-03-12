<?php

namespace Decima\Doc\Test;

use PHPUnit\Framework\TestCase;

class MathTest extends TestCase
{
    public function testSum()
    {
        $math = new \Decima\Doc\Math();
        $this->assertEquals(5, $math->sum(2,3));
    }
}
