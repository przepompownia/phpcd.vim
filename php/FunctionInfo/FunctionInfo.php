<?php

namespace PHPCD\FunctionInfo;

interface FunctionInfo
{
    public function getName();

    public function getDocComment();

    public function getParameters();

    public function getFileName();
}
