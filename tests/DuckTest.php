<?php

namespace Example\Lite\Test;

use PHPUnit\Framework\TestCase;

class DuckTest extends TestCase
{
    public function testFly()
    {
        $this->assertEquals('I fly.', $this->createTestObject()->fly());
    }

    public function testQuack()
    {
        $this->assertEquals('I quack.', $this->createTestObject()->quack());
    }

    public function testSwim()
    {
        $this->assertEquals('I swim.', $this->createTestObject()->swim());
    }
    public function testGetName()
    {
        $this->assertEquals('Duck Norris', $this->createTestObject()->getName());
    }

    protected function createTestObject(): \Example\Lite\Duck
    {
        return new \Example\Lite\Duck('Duck Norris', false);
    }
}
