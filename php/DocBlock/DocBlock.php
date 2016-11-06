<?php

namespace PHPCD\DocBlock;

use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Array_;

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

        // @TODO refactor
        if ($tag instanceof Compound) {
            $index = 0;
            while ($tag->has($index)) {
                $type = $tag->get($index);
                if ($type instanceof Object_) {
                    $types[] = (string)$type;
                } else {
                    printf("%s: %s\n", get_class($type), (string)$type);
                }

                ++$index;
            }
        } elseif ($tag instanceof Array_) {

        }

        return $types;
    }
}
