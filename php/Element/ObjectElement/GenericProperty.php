<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\Element\ClassInfo\ClassInfo;

class GenericProperty implements PropertyInfo
{
    private $name;

    private $access;

    private $static = false;

    /**
     * @var ClassInfo
     */
    private $classInfo;

    private $docComment = '';

    public function __construct($name, ClassInfo $classInfo, $access, $static = false, $docComment = '')
    {
        $this->name = $name;
        $this->classInfo = $classInfo;
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

    /**
     * @return ClassInfo
     */
    public function getClass()
    {
        return $this->classInfo;
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

    /**
     * @return array
     */
    public function getAllowedTypes()
    {
    }

    /**
     * @return array
     */
    public function getAllowedNonTrivialTypes()
    {
    }
}
