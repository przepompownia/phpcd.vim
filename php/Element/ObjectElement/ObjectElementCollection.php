<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\Collection\Collection;
use PHPCD\View\ObjectElementVisitor;

/**
 * @method ObjectElement[] getIterator()
 */
class ObjectElementCollection extends Collection
{
    /**
     * @var ObjectElement[]
     */
    protected $collection = [];

    /**
     * @param ObjectElement $objectElement
     *
     * @return $this
     */
    public function add(ObjectElement $objectElement)
    {
        $this->collection[$objectElement->getName()] = $objectElement;

        return $this;
    }

    public function acceptObjectElement(ObjectElementVisitor $visitor)
    {
        $visitor->visitCollection($this);
    }
}
