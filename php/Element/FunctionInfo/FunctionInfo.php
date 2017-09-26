<?php

namespace PHPCD\Element\FunctionInfo;

use PHPCD\View\FunctionVisitor;

interface FunctionInfo
{
    public function getName();

    public function getNamespaceName();

    public function getDocComment();

    public function getParameters();

    public function getFileName();

    public function getStartLine();

    public function getNonTrivialTypes(): array;

    public function accept(FunctionVisitor $visitor);
}
