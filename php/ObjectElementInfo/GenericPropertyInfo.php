<?php

namespace PHPCD\ObjectElementInfo;

class GenericPropertyInfo implements PropertyInfo
{
    private $name;

    private $access;

    private $static = false;

    private $class;

    private $docComment = '';

    public function __construct($name, $class, $access, $static = false, $docComment = '')
    {
        $this->name = $name;
        $this->class = $class;
        $this->setAccess($access);
        $this->static = $static;
        $this->docComment = $docComment;
    }

    private function setAccess($access)
    {
        if (in_array($access, ['public', 'protected'])) {
            $this->access = $access;
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function isPublic()
    {
        return 'public' === $this->access;
    }

    public function isProtected()
    {
        return 'protected' === $this->access;
    }

    public function isStatic()
    {
        return $this->static;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getDocComment()
    {
        return $this->docComment;
    }

    public function getModifiers()
    {
        $modifiers = [];

        if ($this->isPublic()) {
            $modifiers[] = 'public';
        }

        if ($this->isProtected()) {
            $modifiers[] = 'protected';
        }

        if ($this->isStatic()) {
            $modifiers[] = 'static';
        }

        return $modifiers;
    }
}
