<?php

namespace PHPCD\Element;

interface Element
{
    public function accept(CollectionVisitor $visitor);
}
