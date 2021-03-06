<?php

namespace PHPCD;

use PHPCD\Element\ConstantInfo\ConstantRepository;
use PHPCD\Filter\ConstantFilter;
use PHPCD\Filter\FunctionFilter;
use PHPCD\Element\FunctionInfo\FunctionRepository;
use PHPCD\Element\ObjectElement\MethodPath;
use PHPCD\Element\ObjectElement\PropertyPath;
use Psr\Log\LoggerInterface as Logger;
use Psr\Log\LoggerAwareTrait;
use Lvht\MsgpackRpc\Server as RpcServer;
use Lvht\MsgpackRpc\Handler as RpcHandler;
use PHPCD\PHPFile\PHPFileFactory;
use PHPCD\Element\ConstantInfo\ClassConstantRepository;
use PHPCD\Filter\ClassConstantFilter;
use PHPCD\Filter\MethodFilter;
use PHPCD\Filter\PropertyFilter;
use PHPCD\Element\ObjectElement\MethodRepository;
use PHPCD\Element\ObjectElement\PropertyRepository;
use PHPCD\View\View;

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
     * @var ClassConstantRepository
     */
    private $classConstantRepository;

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

    public function __construct(
        NamespaceInfo $nsinfo,
        Logger $logger,
        ConstantRepository $constantRepository,
        ClassConstantRepository $classConstantRepository,
        PropertyRepository $propertyRepository,
        MethodRepository $methodRepository,
        PHPFileFactory $fileFactory,
        View $view,
        FunctionRepository $functionRepository
    ) {
        $this->nsinfo = $nsinfo;
        $this->setLogger($logger);
        $this->fileFactory = $fileFactory;
        $this->constantRepository = $constantRepository;
        $this->classConstantRepository = $classConstantRepository;
        $this->propertyRepository = $propertyRepository;
        $this->methodRepository = $methodRepository;
        $this->view = $view;
        $this->functionRepository = $functionRepository;
    }

    public function setServer(RpcServer $server)
    {
        $this->server = $server;
    }

    /**
     * Fetch class method's source file path
     * and their definition line number.
     *
     * @param string $className  class name
     * @param string $symbol method or function name
     *
     * @return array
     */
    public function findSymbolDeclaration($className, $symbol = '__construct')
    {
        try {
            $reflection = new \ReflectionClass($className);

            if ($reflection->hasMethod($symbol)) {
                $reflection = $reflection->getMethod($symbol);
            } elseif ($reflection->hasConstant($symbol)) {
                return [$this->getConstPath($symbol, $reflection), 'const '.$symbol];
            } elseif ($reflection->hasProperty($symbol)) {
                $line = $this->getPropertyDefLine($reflection, $symbol);

                return [$reflection->getFileName(), $line];
            }

            return [$reflection->getFileName(), $reflection->getStartLine()];
        } catch (\ReflectionException $e) {
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

    private function getPropertyDefLine(\ReflectionClass $classReflection, $property)
    {
        $class = new \SplFileObject($classReflection->getFileName());
        $class->seek($classReflection->getStartLine());

        $pattern = '/(private|protected|public|var)\s\$'.$property.'/x';
        foreach ($class as $line => $content) {
            if (preg_match($pattern, $content)) {
                return $line + 1;
            }
        }

        return $classReflection->getStartLine();
    }

    private function getConstPath($constantName, \ReflectionClass $reflection)
    {
        $origin = $path = $reflection->getFileName();
        $originReflection = $reflection;

        while ($reflection = $reflection->getParentClass()) {
            if ($reflection->hasConstant($constantName)) {
                $path = $reflection->getFileName();
            } else {
                break;
            }
        }

        if ($origin === $path) {
            $interfaces = $originReflection->getInterfaces();
            foreach ($interfaces as $interface) {
                if ($interface->hasConstant($constantName)) {
                    $path = $interface->getFileName();
                    break;
                }
            }
        }

        return $path;
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
        $items = [];
        try {
            if (false !== $isStatic) {
                $constantFilter = new ClassConstantFilter([ClassConstantFilter::CLASS_NAME => $className], $pattern);
                $constants = $this->classConstantRepository->find($constantFilter);

                $items = array_merge($items, $this->view->renderClassConstantCollection($constants));
            }

            $methodFilter = new MethodFilter([
                MethodFilter::CLASS_NAME => $className,
                MethodFilter::PUBLIC_ONLY => $publicOnly,
                MethodFilter::STATIC_ONLY => $isStatic,
            ], $pattern);

            $methods = $this->methodRepository->find($methodFilter);

            $items = array_merge($items, $this->view->renderMethodCollection($methods));

            $propertyFilter = new PropertyFilter([
                PropertyFilter::CLASS_NAME => $className,
                PropertyFilter::PUBLIC_ONLY => $publicOnly,
                PropertyFilter::STATIC_ONLY => $isStatic,
            ], $pattern);

            $properties = $this->propertyRepository->find($propertyFilter);

            $items = array_merge($items, $this->view->renderPropertyCollection($properties));
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }

        return $items;
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
