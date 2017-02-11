<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\View\ConstantVisitor;

interface ConstantInfo
{
    public function getName();

    public function getValue();

    public function accept(ConstantVisitor $visitor);
}
