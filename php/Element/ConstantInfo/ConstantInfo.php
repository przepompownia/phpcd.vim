<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\View\ConstantInfoVisitor;

interface ConstantInfo
{
    public function getName();

    public function getValue();

    public function accept(ConstantInfoVisitor $visitor);
}
