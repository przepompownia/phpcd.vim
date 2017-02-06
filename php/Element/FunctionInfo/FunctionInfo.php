<?php

namespace PHPCD\Element\FunctionInfo;

use PHPCD\Element\Element;
use PHPCD\Element\CollectionVisitor;
use PHPCD\View\FunctionInfoVisitor;

interface FunctionInfo
{
    public function getName();

    public function getDocComment();

    public function getParameters();

    public function getFileName();

    public function getReturnTypes();

    public function getStartLine();

    public function accept(FunctionInfoVisitor $visitor);
}
