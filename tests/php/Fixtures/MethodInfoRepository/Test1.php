<?php

namespace PHPCD\MethodInfoRepository;

class Test1 extends Sup implements ITest1
{
    const ZZZ = 'vvv';

    public $pub1;

    protected $pub2;

    private $pub3;

    public function play()
    {
    }

    public function run()
    {
    }

    protected function foo()
    {
        $this->bar();
    }

    private function bar()
    {
    }
}
