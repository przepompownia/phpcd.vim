<?php

namespace tests\Fixtures\MethodRepository;

use PHPCD\PatternMatcher as PM;

class Sup
{
    protected $pub2;

    protected $pub4;

    /**
     * @var \ReflectionClass|Test1
     */
    public $pub5;

    /**
     * @var PM\PatternMatcher
     */
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

    /**
     * @return \ReflectionClass|Test1
     */
    public function doNothing(): PM\PatternMatcher
    {

    }
}
