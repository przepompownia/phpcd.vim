<?php

namespace tests\MethodInfoRepository;

class Sup
{
    protected $pub2;

    protected $pub4;

    /**
     * @var \ReflectionClass|Test1
     */
    public $pub5;

    private $pub6;

    /**
     * @return \ReflectionClass|Test1
     */
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
