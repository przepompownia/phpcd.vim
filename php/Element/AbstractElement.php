<?php

namespace PHPCD\Element;

class AbstractElement implements Element
{
    public function accept(CollectionVisitor $visitor)
    {
        $visitor->visitElement($this);
    }
}
