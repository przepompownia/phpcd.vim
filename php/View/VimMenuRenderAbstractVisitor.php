<?php

namespace PHPCD\View;

use PHPCD\Element\ObjectElementInfo\ObjectElementInfo;
use PHPCD\Collection\Collection;
use PHPCD\Element\CollectionVisitor;

abstract class VimMenuRenderAbstractVisitor implements CollectionVisitor
{
    /**
     *  @var array Map between modifier numbers and displayed symbols
     */
    private $modifier_symbols = [
       'final' => '!',
       'private' => '-',
       'protected' => '#',
       'public' => '+',
       'static' => '@',
    ];

    protected function getModifiers(ObjectElementInfo $objectElement)
    {
        return implode('', array_intersect_key($this->modifier_symbols, array_flip($objectElement->getModifiers())));
    }
    protected $output = [];

    public function getOutput()
    {
        return $this->output;
    }
}
