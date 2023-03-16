<?php

namespace Example\Lite;

class Duck extends Animal
{
    public function fly(): string
    {
        return 'I fly.';
    }

    public function forceFly(): string
    {
        return 'I very-very fly!!';
    }

    public function quack(): string
    {
        return 'I quack.';
    }

    public function swim(): string
    {
        return 'I swim.';
    }
}
