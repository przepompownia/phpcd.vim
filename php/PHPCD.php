<?php

namespace PHPCD;

use Lvht\MsgpackRpc\Handler as RpcHandler;
use Lvht\MsgpackRpc\Server as RpcServer;
use PHPCD\Element\ConstantInfo\ConstantRepository;
use PHPCD\Element\FunctionInfo\FunctionRepository;
use PHPCD\Element\ObjectElement\CompoundObjectElementRepository;
use PHPCD\Element\ObjectElement\MethodPath;
use PHPCD\Element\ObjectElement\MethodRepository;
use PHPCD\Element\ObjectElement\PropertyPath;
use PHPCD\Element\ObjectElement\PropertyRepository;
use PHPCD\Filter\ConstantFilter;
use PHPCD\Filter\FunctionFilter;
use PHPCD\PHPFile\PHPFileFactory;
use PHPCD\View\View;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface as Logger;

class PHPCD implements RpcHandler
{
    use LoggerAwareTrait;

    /**
     * @var RpcServer
     */
    private $server;

    /**
     * @var NamespaceInfo
     */
    private $nsinfo;

    /**
     * @var ConstantRepository
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

    /**
     * @var View
     */
    private $view;

    /**
     * @var FunctionRepository
     */
    private $functionRepository;

    /**
     * Probably it should be replaced by
     * correctly implemented repository
     * to avoid scanning each file each time
     * even if such was not changed in meantime.
     *
     * @var PHPFileFactory
     */
    private $fileFactory;

    /**
     * @var CompoundObjectElementRepository
     */
    private $objectElementRepository;

    public function __construct(
        NamespaceInfo $nsinfo,
        Logger $logger,
        ConstantRepository $constantRepository,
        PropertyRepository $propertyRepository,
        MethodRepository $methodRepository,
        PHPFileFactory $fileFactory,
        View $view,
        FunctionRepository $functionRepository,
        CompoundObjectElementRepository $objectElementRepository
    ) {
        $this->nsinfo = $nsinfo;
        $this->setLogger($logger);
        $this->fileFactory = $fileFactory;
        $this->constantRepository = $constantRepository;
        $this->propertyRepository = $propertyRepository;
        $this->methodRepository = $methodRepository;
        $this->view = $view;
        $this->functionRepository = $functionRepository;
        $this->objectElementRepository = $objectElementRepository;
    }

    public function setServer(RpcServer $server)
    {
        $this->server = $server;
    }

    /**
     * Fetch class method's source file path
     * and their definition line number.
     */
    public function findSymbolDeclaration($className, $symbol = '__construct'): array
    {
        try {
            $symbol = $this->objectElementRepository->findObjectElement($className, $symbol);

            $location = $symbol->getPhysicalLocation();

//            $this->logger->debug($location->getFileName());

            return [$location->getFileName(), $location->getLineNumber()];
            // @todo render
        } catch (NotFoundException $e) {
            return ['', null];
        }
    }

    public function locateFunctionDeclaration($functionName)
    {
        try {
            $reflection = $this->functionRepository->get($functionName);

            return [$reflection->getFileName(), $reflection->getStartLine()];
        } catch (\ReflectionException $e) {
            return ['', null];
        }
    }

    /**
     * Fetch the php script's namespace and imports(by use) list.
     *
     * @param string $path the php script path
     */
    public function getPHPFileInfo($path)
    {
        $file = $this->fileFactory->createFile($path);

        return $this->view->renderPHPFile($file);
    }

    public function getTypesReturnedByFunction($functionName)
    {
        $function = $this->functionRepository->get($functionName);

        return $function->getNonTrivialTypes();
    }

    public function getTypesReturnedByMethod($className, $methodName)
    {
        $methodPath = new MethodPath($className, $methodName);
        $method = $this->methodRepository->getByPath($methodPath);

        return $method->getNonTrivialTypes();
    }

    /**
     * Fetch class attribute's type by `@var` annotation.
     *
     * @return array array of types
     */
    public function getTypesOfProperty($className, $propertyName)
    {
        $propertyPath = new PropertyPath($className, $propertyName);
        $property = $this->propertyRepository->getByPath($propertyPath);

        return $property->getNonTrivialTypes();
    }

    public function getMatchingClassDetails($className, $pattern, $isStatic, $publicOnly = true)
    {
        $matchingElements = $this->objectElementRepository
            ->getMatchingClassDetails($className, $pattern, $isStatic, $publicOnly);

        return $this->view->renderObjectElementCollection($matchingElements);
    }

    public function getFixForNewClassUsage($path, array $newClassParams)
    {
        $info = $this->fileFactory->createFile($path);

        return $info->getFixForNewClassUsage($newClassParams);
    }

    public function getFunctionsAndConstants($pattern)
    {
        return array_merge($this->findFunctions($pattern), $this->findConstants($pattern));
    }

    private function findFunctions($pattern)
    {
        $functions = $this->functionRepository->find(new FunctionFilter($pattern));

        return $this->view->renderFunctionCollection($functions);
    }

    private function findConstants($pattern)
    {
        $constants = $this->constantRepository->find(new ConstantFilter($pattern));

        return $this->view->renderConstantCollection($constants);
    }

    /**
     * generate psr4 namespace according composer.json and file path.
     */
    public function psr4ns($filePath)
    {
        return $this->nsinfo->getByPath($filePath);
    }
}
