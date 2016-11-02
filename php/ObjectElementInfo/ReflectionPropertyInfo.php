<?php

namespace PHPCD\ObjectElementInfo;

use PHPCD\ObjectElementInfo\PropertyInfo;
use phpDocumentor\Reflection\DocBlockFactory;

class ReflectionPropertyInfo extends ReflectionObjectElementInfo implements PropertyInfo
{
    /**
     * @var DocBlockFactory
     */
    protected $docBlockFactory;

    public function __construct(\ReflectionProperty $property, DocBlockFactory $docBlockFactory)
    {
        $this->objectElement = $property;
        $this->docBlockFactory = $docBlockFactory;
    }

    /**
     * @return array
     */
    public function getAllowedTypes()
    {
        $commentText = $this->getDocComment();
        $comment = $this->docBlockFactory->create($commentText);
        $tags = $comment->getTagsByName('var');

        // Get only the first @var line
        $tag = $tags[0];
        return $tag;
    }

    /**
     * @return array
     */
    public function getAllowedNonTrivialTypes()
    {
        return [];
    }
}
