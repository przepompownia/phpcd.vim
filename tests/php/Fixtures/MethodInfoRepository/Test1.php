<?php

namespace PHPCD\MethodInfoRepository;

/**
 * @property-read \SplFileObject $file
 * @property-write \SplFileObject $pub2
 * @property \ReflectionClass $pub7
 */
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
