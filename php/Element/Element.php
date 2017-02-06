<?php

namespace PHPCD\Element;

use PHPCD\Element\CollectionVisitor;

interface Element
{
    public function accept(CollectionVisitor $visitor);
}