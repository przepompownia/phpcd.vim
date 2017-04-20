<?php

namespace PHPCD\DocBlock;

use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Static_;
use phpDocumentor\Reflection\Types\This;

class DocBlock
{
    const TAG_VAR    = 'var';
    const TAG_PARAM  = 'param';
    const TAG_RETURN = 'return';

    const OBJECT_TYPES = [
        Self_::class,
        Static_::class,
        This::class,
        Object_::class,
    ];

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * @var DocBlockFactoryInterface
     */
    protected $docBlockFactory;

    public function __construct(
        DocBlockFactoryInterface $docBlockFactory,
        ContextFactory $contextFactory
    ) {
        $this->docBlockFactory = $docBlockFactory;
        $this->contextFactory = $contextFactory;
    }

    public function getTypesFromDocBlock($docBlockString, $namespace, $fileName, $tagName)
    {
        if (!file_exists($fileName)
            || empty($docBlockString)
            || empty($tagName)
        ) {
            return [];
        }

        $context = $this->contextFactory->createForNamespace($namespace, file_get_contents($fileName));
        $comment = $this->docBlockFactory->create($docBlockString, $context);
        $tags = $comment->getTagsByName($tagName);

        // Get only the first @var line
        $type = $tags[0]->getType();

        if ($type instanceof Compound) {
            return iterator_to_array(new CompoundTypeIterator($type));
        }

        return [$type];
    }

    public function getObjectTypesFromDocblock($docBlockString, $namespace, $fileName, $tagName)
    {
        $types = [];

        $allTypes = $this->getTypesFromDocBlock($docBlockString, $namespace, $fileName, $tagName);

        foreach ($allTypes as $type) {
            if (in_array(get_class($type), self::OBJECT_TYPES)) {
                $types[] = $type;
            }
        }

        return $types;
    }

    public function getFirstTypeStringFromDocblock($docBlockString, $namespace, $fileName, $tagName)
    {
        $types = $this->getObjectTypesFromDocblock($docBlockString, $namespace, $fileName, $tagName);

        return (string) current($types);
    }
}
