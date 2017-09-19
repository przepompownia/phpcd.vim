<?php

declare(strict_types=1);

namespace PHPCD\Element\ObjectElement;

use PHPCD\Element\ObjectElement\Constant\ClassConstantRepository;
use PHPCD\NotFoundException;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface as Logger;

class CompoundObjectElementRepository
{
    use LoggerAwareTrait;

    /**
     * @var ClassConstantRepository
     */
    private $classConstantRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * @var MethodRepository
     */
    private $methodRepository;

    public function __construct(
        ClassConstantRepository $classConstantRepository,
        PropertyRepository $propertyRepository,
        MethodRepository $methodRepository,
        Logger $logger
    ) {
        $this->classConstantRepository = $classConstantRepository;
        $this->propertyRepository = $propertyRepository;
        $this->methodRepository = $methodRepository;
        $this->logger = $logger;
    }

    /**
     * @todo move to separate class
     */
    public function findObjectElement($className, $symbol = '__construct'): ObjectElement
    {
        try {
            return $this->methodRepository->getByPath(new MethodPath($className, $symbol));
        } catch (NotFoundException $e) {
            try {
                return $this->classConstantRepository->getByPath(new ClassConstantPath($className, $symbol));
            } catch (NotFoundException $e) {
                try {
                    return $this->propertyRepository->getByPath(new PropertyPath($className, $symbol));
                } catch (NotFoundException $e) {
                    throw new NotFoundException(sprintf('Symbol %s not found in class %s', $className, $symbol));
                }
            }
        }
    }
}
