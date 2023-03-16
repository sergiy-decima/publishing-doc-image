<?php

namespace Decima\Doc;

class Hello
{
    private $name;
    public function __construct()
    {
        $this->name = 'Valera';
    }
    public function hello(): string
    {
        return 'hi';
    }

    public function go(): string
    {
        return 'home';
    }
}

class A
{
    public function hello(): string
    {
        return 'hi';
    }
}
