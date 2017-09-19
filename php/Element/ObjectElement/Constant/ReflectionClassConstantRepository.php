<?php

namespace PHPCD\Element\ObjectElement\Constant;

use PHPCD\Element\ClassInfo\ReflectionClassFactory;
use PHPCD\Element\ObjectElement\ClassConstantPath;
use PHPCD\Element\ObjectElement\ReflectionObjectElementRepository;
use PHPCD\Filter\ClassConstantFilter;
use PHPCD\NotFoundException;

class ReflectionClassConstantRepository extends ReflectionObjectElementRepository implements ClassConstantRepository
{
    /**
     * @var ReflectionClassFactory
     */
    protected $classInfoFactory;

    public function find(ClassConstantFilter $filter): ClassConstantCollection
    {
        $classInfo = $this->classInfoFactory->createFromFilter($filter);

        $collection = new ClassConstantCollection();

        foreach (array_keys($classInfo->getConstants()) as $constantName) {
            $constant = new ReflectionClassConstant(
                $this->docBlock,
                new \ReflectionClassConstant($classInfo->getName(), $constantName)
            );
            if (true === $this->filter($constant, $filter)) {
                $collection->add($constant);
            }
        }

        return $collection;
    }

    public function getByPath(ClassConstantPath $path): ClassConstant
    {
        try {
            $reflectionConstant = new \ReflectionClassConstant($path->getClassName(), $path->getElementName());

            return new ReflectionClassConstant($this->docBlock, $reflectionConstant);
        } catch (\ReflectionException $e) {
            throw new NotFoundException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
