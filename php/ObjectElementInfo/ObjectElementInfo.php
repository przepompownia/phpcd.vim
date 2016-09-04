<?php

namespace PHPCD\ObjectElementInfo;

interface ObjectElementInfo
{
    public function getName();

    public function isPublic();

    public function isProtected();

    public function isStatic();

    public function getClass();

    public function getDocComment();

    public function getModifiers();
}
