<?php

declare(strict_types=1);

namespace PHPCD\Element\ObjectElement;

use PHPCD\Element\ObjectElement\Constant\ClassConstantRepository;
use PHPCD\Filter\ClassConstantFilter;
use PHPCD\Filter\MethodFilter;
use PHPCD\Filter\PropertyFilter;
use PHPCD\NotFoundException;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface as Logger;

class CompoundObjectElementRepository
{
    use LoggerAwareTrait;

    /**
     * @var ClassConstantRepository
     */
    private $constantRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * @var MethodRepository
     */
    private $methodRepository;

    public function __construct(
        ClassConstantRepository $constantRepository,
        PropertyRepository $propertyRepository,
        MethodRepository $methodRepository,
        Logger $logger
    ) {
        $this->constantRepository = $constantRepository;
        $this->propertyRepository = $propertyRepository;
        $this->methodRepository = $methodRepository;
        $this->logger = $logger;
    }

    public function findObjectElement(ObjectElementPath $path): ObjectElement
    {
        try {
            return $this->methodRepository->getByPath($path);
        } catch (NotFoundException $e) {
            try {
                return $this->constantRepository->getByPath($path);
            } catch (NotFoundException $e) {
                try {
                    return $this->propertyRepository->getByPath($path);
                } catch (NotFoundException $e) {
                    throw new NotFoundException(sprintf(
                        'Symbol %s not found in class %s',
                        $path->getClassName(),
                        $path->getElementName()
                    ));
                }
            }
        }
    }

    public function getMatchingClassDetails(
        string $className,
        string $pattern,
        bool $isStatic,
        bool $publicOnly = true
    ): ObjectElementCollection {
        $collection = new ObjectElementCollection();

        try {
            if (false !== $isStatic) {
                $constantFilter = new ClassConstantFilter([ClassConstantFilter::CLASS_NAME => $className], $pattern);
                $constants = $this->constantRepository->find($constantFilter);

                foreach ($constants as $constant) {
                    $collection->add($constant);
                }
            }

            $methodFilter = new MethodFilter([
                MethodFilter::CLASS_NAME => $className,
                MethodFilter::PUBLIC_ONLY => $publicOnly,
                MethodFilter::STATIC_ONLY => $isStatic,
            ], $pattern);

            $methods = $this->methodRepository->find($methodFilter);

            foreach ($methods as $method) {
                $collection->add($method);
            }

            $propertyFilter = new PropertyFilter([
                PropertyFilter::CLASS_NAME => $className,
                PropertyFilter::PUBLIC_ONLY => $publicOnly,
                PropertyFilter::STATIC_ONLY => $isStatic,
            ], $pattern);

            $properties = $this->propertyRepository->find($propertyFilter);

            foreach ($properties as $property) {
                $collection->add($property);
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }

        return $collection;
    }

    public function getTypesReturnedByMethod(ObjectElementPath $methodPath): array
    {
        $method = $this->methodRepository->getByPath($methodPath);

        return $method->getNonTrivialTypes();
    }

    public function getTypesOfProperty(ObjectElementPath $propertyPath): array
    {
        $property = $this->propertyRepository->getByPath($propertyPath);

        return $property->getNonTrivialTypes();
    }
}
