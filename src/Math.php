<?php

namespace Decima\Doc;

class Math
{
    public function sum(int|float $a, int|float $b): int|float
    {
        return $a + $b + 1;
    }

    public function hello(): string
    {
        return 'hi';
    }
}
