<?php

namespace PHPCD\A;

class Alpha
{
    const PI = 3.141;

    const INV = 1;

    const iks = 'x';

    public static $staticVar;

    public $pubvar;

    private $privateKey;

    /**
     * @return $this
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;

        return $this;
    }
}
