<?php

namespace PHPCD\MethodInfoRepository;

class Sup
{
    protected $pub2;

    protected $pub4;

    /**
     * @var \ReflectionClass
     */
    public $pub5;

    private $pub6;

    private function baz()
    {
    }

    protected function foo()
    {
        $this->baz();
    }

    protected function xyz()
    {
    }
}
