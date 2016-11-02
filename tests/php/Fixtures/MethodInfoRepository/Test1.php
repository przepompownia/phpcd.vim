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

    /**
     * @var int|$this|\PHPCD\PHPCD|Sup|self[] Chaos
     */
    public $pub1;

    /**
     * @var \PHPCD\PHPID
     */
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
