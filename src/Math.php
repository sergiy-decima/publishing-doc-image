<?php

namespace Decima\Doc;

class Math
{
    public function sum(int|float $a, int|float $b): int|float
    {
        if (false) {
            return 0;
        }

        return $a + $b;
    }

    public function minus(int|float $a, int|float $b): int|float
    {
        return $a - $b;
    }

    public function multiply(int|float $a, int|float $b): int|float
    {
        return $a * $b;
    }
}
