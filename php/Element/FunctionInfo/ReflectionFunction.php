<?php

namespace PHPCD\Element\FunctionInfo;

use PHPCD\View\FunctionVisitor;
use PHPCD\DocBlock\DocBlock;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Static_;
use phpDocumentor\Reflection\Types\This;
use phpDocumentor\Reflection\Types\Object_;

class ReflectionFunction implements FunctionInfo
{
    /**
     * @var \ReflectionFunction
     */
    private $reflectionFunction;

    /**
     * @var DocBlock
     */
    protected $docBlock;

    public function __construct(DocBlock $docBlock, \ReflectionFunction $reflectionFunction)
    {
        $this->docBlock = $docBlock;
        $this->reflectionFunction = $reflectionFunction;
    }

    public function getNamespaceName()
    {
        return $this->reflectionFunction->getNamespaceName();
    }

    public function getName()
    {
        return $this->reflectionFunction->getName();
    }

    public function getDocComment()
    {
        return $this->reflectionFunction->getDocComment();
    }

    public function getParameters()
    {
        return $this->reflectionFunction->getParameters();
    }

    public function getFileName()
    {
        return $this->reflectionFunction->getFileName();
    }

    public function getStartLine()
    {
        return $this->reflectionFunction->getStartLine();
    }

    /**
     * @return array
     */
    private function getNonTrivialTypesFromDocblock()
    {
        $docBlock = $this->getDocComment();
        $objectTypes = $this->docBlock->getObjectTypesFromDocblock(
            $docBlock,
            $this->getNamespaceName(),
            $this->getFileName(),
            DocBlock::TAG_RETURN
        );

        $types = [];

        foreach ($objectTypes as $type) {
            $typeClass = get_class($type);

            switch ($typeClass) {
                case Object_::class:
                    $types[] = (string) $type;
                    break;
                case This::class:
                case Static_::class:
                case Self_::class:
                    break;
                default:
                    throw new \Exception('Invalid type.');
            }
        }

        return $types;
    }

    public function getNonTrivialTypes(): array
    {
        if ($this->reflectionFunction->hasReturnType()) {
            return [(string) $this->reflectionFunction->getReturnType()];
        }

        return $this->getNonTrivialTypesFromDocblock();
    }

    public function accept(FunctionVisitor $visitor)
    {
        $visitor->visitElement($this);
    }
}
