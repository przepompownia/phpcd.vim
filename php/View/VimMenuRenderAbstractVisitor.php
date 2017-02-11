<?php

namespace PHPCD\View;

use PHPCD\Element\ObjectElement\ObjectElement;
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

    protected function getModifiers(ObjectElement $objectElement)
    {
        return implode('', array_intersect_key($this->modifier_symbols, array_flip($objectElement->getModifiers())));
    }

    protected $output = [];

    public function getOutput()
    {
        return $this->output;
    }
}
