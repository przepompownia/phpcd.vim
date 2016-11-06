<?php

namespace PHPCD\DocBlock;

use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Compound;

class DocBlock
{
    /**
     * @var DocBlockFactory
     */
    protected $docBlockFactory;

    public function __construct(DocBlockFactory $docBlockFactory)
    {
        $this->docBlockFactory = $docBlockFactory;
    }

    public function getTypesFromDocBlock($docBlock)
    {
        $comment = $this->docBlockFactory->create($docBlock);
        $tags = $comment->getTagsByName('var');

        // Get only the first @var line
        $tag = $tags[0]->getType();

        var_dump(explode('|', (string)$tag));

        $types = [];

        if ($tag instanceof Compound) {
            $index = 0;
            while ($types->has($index)) {
                $type = $types->get($index);
                var_dump(sprintf("%s: %s\n", get_class($type), (string)$type));
                $types[] = (string)$type;
                ++$index;
            }
        }

        return $types;
    }
}
