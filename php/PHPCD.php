<?php
namespace PHPCD;

use PHPCD\ConstantInfo\ConstantInfoRepository;
use PHPCD\Filter\ConstantFilter;
use PHPCD\Filter\FunctionFilter;
use PHPCD\FunctionInfo\FunctionRepository;
use PHPCD\ObjectElementInfo\MethodPath;
use PHPCD\ObjectElementInfo\PropertyPath;
use Psr\Log\LoggerInterface as Logger;
use Psr\Log\LoggerAwareTrait;
use Lvht\MsgpackRpc\Server as RpcServer;
use Lvht\MsgpackRpc\Handler as RpcHandler;
use PHPCD\PHPFileInfo\PHPFileInfoFactory;
use PHPCD\ClassInfo\ClassInfoFactory;
use PHPCD\ConstantInfo\ClassConstantInfoRepository;
use PHPCD\Filter\ClassConstantFilter;
use PHPCD\Filter\MethodFilter;
use PHPCD\Filter\PropertyFilter;
use PHPCD\ObjectElementInfo\MethodInfoRepository;
use PHPCD\ObjectElementInfo\PropertyInfoRepository;
use PHPCD\View\View;
use PHPCD\FunctionInfo\FunctionInfo;
use PHPCD\DocBlock\LegacyTypeLogic;

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
     * @var ClassConstantInfoRepository
     */
    private $classConstantRepository;

    /**
     * @var ConstantInfoRepository
     */
    private $constantRepository;

    /**
     * @var PropertyInfoRepository
     */
    private $propertyInfoRepository;

    /**
     * @var MethodInfoRepository
     */
    private $methodInfoRepository;

    /**
     * @var View
     */
    private $view;

    /**
     * @var LegacyTypeLogic
     */
    private $legacyTypeLogic;

    /**
     * @var FunctionRepository
     */
    private $functionRepository;

    /*
     * Probably it should be replaced by
     * correctly implemented repository
     * to avoid scanning each file each time
     * even if such was not changed in meantime.
     *
     * @var PHPFileInfoFactory
     */
    private $fileInfoFactory;

    public function __construct(
        NamespaceInfo $nsinfo,
        Logger $logger,
        ConstantInfoRepository $constantRepository,
        ClassConstantInfoRepository $classConstantRepository,
        PropertyInfoRepository $propertyRepository,
        MethodInfoRepository $methodInfoRepository,
        PHPFileInfoFactory $fileInfoFactory,
        View $view,
        FunctionRepository $functionRepository,
        LegacyTypeLogic $legacyTypeLogic
    ) {
        $this->nsinfo = $nsinfo;
        $this->setLogger($logger);
        $this->fileInfoFactory = $fileInfoFactory;
        $this->constantRepository = $constantRepository;
        $this->classConstantRepository = $classConstantRepository;
        $this->propertyInfoRepository = $propertyRepository;
        $this->methodInfoRepository = $methodInfoRepository;
        $this->view = $view;
        $this->functionRepository = $functionRepository;
        $this->legacyTypeLogic = $legacyTypeLogic;
    }

    public function setServer(RpcServer $server)
    {
        $this->server = $server;
    }

    /**
     * Fetch class method's source file path
     * and their definition line number.
     *
     * @param string $className class name
     * @param string $methodName method or function name
     *
     * @return [path, line]
     */
    public function findSymbolDeclaration($className, $methodName = '__construct')
    {
        try {
            $reflection = new \ReflectionClass($className);

            if ($reflection->hasMethod($methodName)) {
                $reflection = $reflection->getMethod($methodName);
            } elseif ($reflection->hasConstant($methodName)) {
                return [$this->getConstPath($methodName, $reflection), 'const ' . $methodName];
            } elseif ($reflection->hasProperty($methodName)) {
                $line = $this->getPropertyDefLine($reflection, $methodName);
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

    private function getPropertyDefLine($classReflection, $property)
    {
        $class = new \SplFileObject($classReflection->getFileName());
        $class->seek($classReflection->getStartLine());

        $pattern = '/(private|protected|public|var)\s\$' . $property . '/x';
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
     *
     */
    public function nsuse($path)
    {
        $fileInfo = $this->fileInfoFactory->createFileInfo($path);
        return $this->view->renderPHPFileInfo($fileInfo);
    }

    public function getTypesReturnedByFunction($functionName)
    {
        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            $type = $this->legacyTypeLogic->typeByReturnType('', $functionName);
            if ($type) {
                return [$type];
            }
        }

        list($path, $doc) = $this->legacyTypeLogic->docFunction($functionName);
        return $this->legacyTypeLogic->typeByDoc($path, $doc);
    }

    public function getTypesReturnedByMethod($className, $methodName)
    {
        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            $type = $this->legacyTypeLogic->typeByReturnType($className, $methodName);
            if ($type) {
                return [$type];
            }
        }

        $methodPath = new MethodPath($className, $methodName);
        $method     = $this->methodInfoRepository->getByPath($methodPath);
        $path       = $method->getClass()->getFileName();
        $doc        = $method->getDocComment();

        $types = $this->legacyTypeLogic->typeByDoc($path, $doc);

        return $types;
    }

    /**
     * Fetch class attribute's type by @var annotation
     *
     * @return [type1, type2, ...]
     */
    public function getTypesOfProperty($className, $propertyName)
    {
        $propertyPath   = new PropertyPath($className, $propertyName);
        $property       = $this->propertyInfoRepository->getByPath($propertyPath);
        $path           = $property->getClass()->getFileName();
        $doc            = $property->getDocComment();

        $types = $this->legacyTypeLogic->typeByDoc($path, $doc);

        return $types;
    }

    public function getMatchingClassDetails($className, $pattern, $isStstic, $publicOnly = true)
    {
        try {
            $items = [];

            if (false !== $isStstic) {
                $constantFilter = new ClassConstantFilter([ClassConstantFilter::className => $className], $pattern);
                $constants = $this->classConstantRepository->find($constantFilter);

                foreach ($constants as $constant) {
                        $items[] = $this->view->renderConstantInfo($constant);
                }
            }

            $methodFilter = new MethodFilter([
                MethodFilter::className    => $className,
                MethodFilter::publicOnly   => $publicOnly,
                MethodFilter::STATIC_ONLY   => $isStstic,
            ], $pattern);

            $methods = $this->methodInfoRepository->find($methodFilter);

            foreach ($methods as $method) {
                $items[] = $this->view->renderMethodInfo($method);
            }

            $propertyFilter = new PropertyFilter([
                PropertyFilter::className    => $className,
                PropertyFilter::publicOnly   => $publicOnly,
                PropertyFilter::STATIC_ONLY   => $isStstic,
            ], $pattern);

            $properties = $this->propertyInfoRepository->find($propertyFilter);

            foreach ($properties as $property) {
                $items[] = $this->view->renderPropertyInfo($property);
            }

            return $items;
        } catch (\ReflectionException $e) {
            $this->logger->debug($e->getMessage());
            return [null, []];
        }
    }

    public function getFixForNewClassUsage($path, array $newClassParams)
    {
        $info = $this->fileInfoFactory->createFileInfo($path);

        return $info->getFixForNewClassUsage($newClassParams);
    }

    public function getFunctionsAndConstants($pattern)
    {
        return array_merge($this->findFunctions($pattern), $this->findConstants($pattern));
    }

    private function findFunctions($pattern)
    {
        $functions = $this->functionRepository->find(new FunctionFilter($pattern));

        return $this->view->renderFunctionInfoCollection($functions);
    }

    private function findConstants($pattern)
    {
        $constants = $this->constantRepository->find(new ConstantFilter($pattern));

        return $this->view->renderConstantInfoCollection($constants);
    }

    /**
     * generate psr4 namespace according composer.json and file path
     */
    public function psr4ns($path)
    {
        return $this->nsinfo->getByPath($path);
    }
}
