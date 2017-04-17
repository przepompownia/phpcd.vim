<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\Element\ClassInfo\ClassInfo;
use PHPCD\Element\ClassInfo\ReflectionClass;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Static_;
use phpDocumentor\Reflection\Types\This;
use phpDocumentor\Reflection\Types\Object_;
use PHPCD\DocBlock\DocBlock;

abstract class ReflectionObjectElement implements ObjectElement
{
    /**
     * @var DocBlock
     */
    protected $docBlock;

    public function __construct(DocBlock $docBlock)
    {
        $this->docBlock = $docBlock;
    }

    abstract protected function getDocBlockTagName();

    /**
     * @var \ReflectionMethod|\ReflectionProperty
     */
    protected $objectElement;

    /**
     * @var ReflectionClass;
     */
    protected $classInfo;

    /**
     * @return ClassInfo
     */
    public function getClass()
    {
        if (null === $this->classInfo) {
            $this->classInfo = new ReflectionClass($this->objectElement->getDeclaringClass());
        }

        return $this->classInfo;
    }

    public function getName()
    {
        return $this->objectElement->getName();
    }

    public function isPublic()
    {
        return $this->objectElement->isPublic();
    }

    public function isProtected()
    {
        return $this->objectElement->isProtected();
    }

    public function isStatic()
    {
        return $this->objectElement->isStatic();
    }

    public function getDocComment()
    {
        return $this->objectElement->getDocComment();
    }

    public function getModifiers()
    {
        return \Reflection::getModifierNames($this->objectElement->getModifiers());
    }

    /**
     * @return array
     */
    public function getAllowedTypes()
    {
        $docBlock = $this->getDocComment();

        return $this->docBlock->getTypesFromDocBlock(
            $docBlock,
            $this->getClass()->getNamespaceName(),
            $this->getClass()->getFileName(),
            $this->getDocBlockTagName()
        );
    }

    /**
     * @return string
     */
    public function getFirstTypeString()
    {
        $docBlock = $this->getDocComment();

        return $this->docBlock->getFirstTypeStringFromDocblock(
            $docBlock,
            $this->getClass()->getNamespaceName(),
            $this->getClass()->getFileName(),
            $this->getDocBlockTagName()
        );
    }

    /**
     * @return array
     */
    protected function getNonTrivialTypesFromDocblock()
    {
        $docBlock = $this->getDocComment();
        $objectTypes = $this->docBlock->getObjectTypesFromDocblock(
            $docBlock,
            $this->getClass()->getNamespaceName(),
            $this->getClass()->getFileName(),
            $this->getDocBlockTagName()
        );

        $types = [];

        foreach ($objectTypes as $type) {
            $typeClass = get_class($type);

            switch ($typeClass) {
                case This::class:
                // @todo replace by correct class names
                case Static_::class:
                case Self_::class:
                    $types[] = $this->getClass()->getName();
                    break;
                case Object_::class:
                    $types[] = (string) $type;
                    break;
                default:
                    throw new \Exception('Invalid type.');
            }
        }

        return $types;
    }
}
