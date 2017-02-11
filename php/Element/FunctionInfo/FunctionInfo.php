<?php

namespace PHPCD\Element\FunctionInfo;

use PHPCD\View\FunctionVisitor;

interface FunctionInfo
{
    public function getName();

    public function getDocComment();

    public function getParameters();

    public function getFileName();

    public function getReturnTypes();

    public function getStartLine();

    public function accept(FunctionVisitor $visitor);
}
