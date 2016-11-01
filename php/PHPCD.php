<?php
namespace PHPCD;

use Psr\Log\LoggerInterface as Logger;
use Psr\Log\LoggerAwareTrait;
use Lvht\MsgpackRpc\Server as RpcServer;
use Lvht\MsgpackRpc\Handler as RpcHandler;
use PHPCD\PHPFileInfo\PHPFileInfoFactory;
use PHPCD\ClassInfo\ClassInfoFactory;
use PHPCD\ConstantInfo\ConstantInfoRepository;
use PHPCD\Filter\ConstantFilter;
use PHPCD\Filter\MethodFilter;
use PHPCD\Filter\PropertyFilter;
use PHPCD\ObjectElementInfo\MethodInfoRepository;
use PHPCD\ObjectElementInfo\PropertyInfoRepository;
use PHPCD\View\View;
use PHPCD\FunctionInfo\FunctionInfo;

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

    /** @var ConstantInfoRepository */
    private $constantInfoRepository;

    /** @var PropertyInfoRepository **/
    private $propertyInfoRepository;

    /** @var MethodInfoRepository **/
    private $methodInfoRepository;

    /** @var View **/
    private $view;

    /*
     * Probably it should be replaced by
     * correctly implemented repository
     * to avoid scanning each file each time
     * even if such was not changed in meantime.
     *
     * @var PHPFileInfoFactory
     */
    private $file_info_factory;

    public function __construct(
        NamespaceInfo $nsinfo,
        Logger $logger,
        ConstantInfoRepository $constantRepository,
        PropertyInfoRepository $propertyRepository,
        MethodInfoRepository $methodInfoRepository,
        PHPFileInfoFactory $file_info_factory,
        View $view
    ) {
        $this->nsinfo = $nsinfo;
        $this->setLogger($logger);
        $this->file_info_factory = $file_info_factory;
        $this->constantInfoRepository = $constantRepository;
        $this->propertyInfoRepository = $propertyRepository;
        $this->methodInfoRepository = $methodInfoRepository;
        $this->view = $view;
    }

    public function setServer(RpcServer $server)
    {
        $this->server = $server;
    }

    /**
     * Fetch function or class method's source file path
     * and their defination line number.
     *
     * @param string $class_name class name
     * @param string $method_name method or function name
     *
     * @return [path, line]
     */
    public function locateMethodOrConstantDeclaration($class_name, $method_name)
    {
        try {
            $reflection = new \ReflectionClass($class_name);

            if ($reflection->hasMethod($method_name)) {
                $reflection = $reflection->getMethod($method_name);
            } elseif ($reflection->hasConstant($method_name)) {
                return [$this->getConstPath($method_name, $reflection), 'const ' . $method_name];
            }

            return [$reflection->getFileName(), $reflection->getStartLine()];
        } catch (\ReflectionException $e) {
            return ['', null];
        }
    }

    public function locateFunctionDeclaration($name)
    {
        try {
            $reflection = new \ReflectionFunction($name);
            return [$reflection->getFileName(), $reflection->getStartLine()];
        } catch (\ReflectionException $e) {
            return ['', null];
        }
    }

    private function getConstPath($const_name, \ReflectionClass $reflection)
    {
        $origin = $path = $reflection->getFileName();
        $origin_reflection = $reflection;

        while ($reflection = $reflection->getParentClass()) {
            if ($reflection->hasConstant($const_name)) {
                $path = $reflection->getFileName();
            } else {
                break;
            }
        }

        if ($origin === $path) {
            $interfaces = $origin_reflection->getInterfaces();
            foreach ($interfaces as $interface) {
                if ($interface->hasConstant($const_name)) {
                    $path = $interface->getFileName();
                    break;
                }
            }
        }

        return $path;
    }

    /**
     * Fetch function, class method or class attribute's docblock
     *
     * @param string $class_name for function set this args to empty
     * @param string $name
     */
    private function doc($class_name, $name, $is_method = true)
    {
        try {
            $reflection_class = null;
            if ($class_name) {
                $reflection = new \ReflectionClass($class_name);
                $reflection_class = $reflection;
                if (!$is_method) {
                    // ReflectionProperty does not have the getFileName method
                    // use ReflectionClass instead
                    $reflection = $reflection->getProperty($name);
                } else {
                    $interfaces = $reflection->getInterfaces();
                    foreach ($interfaces as $interface) {
                        if ($interface->hasMethod($name)) {
                            $reflection = $interface;
                            break;
                        }
                    }

                    $reflection = $reflection->getMethod($name);
                }
            } else {
                $reflection = new \ReflectionFunction($name);
            }

            $doc = $reflection->getDocComment();
            if (preg_match('/@return\s+(static|self|\$this)/i', $doc) && $reflection_class) {
                $path = $reflection_class->getFileName();
            } else {
                $path = $reflection->getFileName();
            }

            return [$path, $this->clearDoc($doc)];
        } catch (\ReflectionException $e) {
            $this->logger->debug($e->getMessage());
            return [null, null];
        }
    }

    /**
     * Fetch the php script's namespace and imports(by use) list.
     *
     * @param string $path the php script path
     *
     */
    public function nsuse($path)
    {
        $file_info = $this->file_info_factory->createFileInfo($path);
        return $this->view->renderPHPFileInfo($file_info);
    }

    /**
     * Fetch the function or class method return value's type
     *
     * For PHP7 or newer version, it tries to use the return type gramar
     * to fetch the real return type.
     *
     * For PHP5, it use the docblock's return or var annotation to fetch
     * the type.
     *
     * @return [type1, type2]
     */
    public function functype($class_name, $name)
    {
        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            $type = $this->typeByReturnType($class_name, $name);
            if ($type) {
                return [$type];
            }
        }

        list($path, $doc) = $this->doc($class_name, $name);
        return $this->typeByDoc($path, $doc);
    }

    /**
     * Fetch class attribute's type by @var annotation
     *
     * @return [type1, type2, ...]
     */
    public function proptype($class_name, $name)
    {
        list($path, $doc) = $this->doc($class_name, $name, false);
        $types = $this->typeByDoc($path, $doc);

        if (!$types) {
            $types = $this->typeByPropertyRead($class_name, $name);
        }

        return $types;
    }

    private function typeByPropertyRead($class_name, $name)
    {
        $reflection = new \ReflectionClass($class_name);
        $doc = $reflection->getDocComment();
        $path = $reflection->getFileName();
        $has_doc = preg_match('/@property(-read|-write)?\s+(\S+)\s+\$?'.$name.'/mi', $doc, $matches);
        if ($has_doc) {
            $types = [$matches[2]];
            return $this->fixRelativeType($path, $types);
        }

        return [];
    }

    private function typeByReturnType($class_name, $name)
    {
        try {
            if ($class_name) {
                $reflection = new \ReflectionClass($class_name);
                $reflection = $reflection->getMethod($name);
            } else {
                $reflection = new \ReflectionFunction($name);
            }
            $type = (string) $reflection->getReturnType();

            if (strtolower($type) == 'self') {
                $type = $class_name;
            }

            return $type;
        } catch (\ReflectionException $e) {
            $this->logger->debug((string) $e);
        }
    }

    private function typeByDoc($class_name, $name)
    {
        list($path, $doc) = $this->doc($class_name, $name);
        $has_doc = preg_match('/@(return|var)\s+(\S+)/m', $doc, $matches);
        if ($has_doc) {
            return $this->fixRelativeType($path, explode('|', $matches[2]));
        }

        return [];
    }

    private function fixRelativeType($path, $names)
    {
        $nsuse = null;

        $types = [];
        foreach ($names as $type) {
            if (isset($this->primitive_types[$type])) {
                continue;
            }

            if (!$nsuse && $type[0] != '\\') {
                $nsuse = $this->nsuse($path);
            }

            if (in_array(strtolower($type), ['static', '$this', 'self'])) {
                $type = $nsuse['namespace'] . '\\' . $nsuse['class'];
            } elseif ($type[0] != '\\') {
                $parts = explode('\\', $type);
                $alias = array_shift($parts);
                if (isset($nsuse['imports'][$alias])) {
                    $type = $nsuse['imports'][$alias];
                    if ($parts) {
                        $type = $type . '\\' . join('\\', $parts);
                    }
                } else {
                    $type = $nsuse['namespace'] . '\\' . $type;
                }
            }

            if ($type) {
                if ($type[0] != '\\') {
                    $type = '\\' . $type;
                }
                $types[] = $type;
            }
        }

        return $types;
    }

    private $primitive_types = [
        'array'    => 1,
        'bool'     => 1,
        'callable' => 1,
        'double'   => 1,
        'float'    => 1,
        'int'      => 1,
        'mixed'    => 1,
        'null'     => 1,
        'object'   => 1,
        'resource' => 1,
        'scalar'   => 1,
        'string'   => 1,
        'void'     => 1,
    ];

    public function getMatchingClassDetails($class_name, $pattern, $is_static, $public_only = true)
    {
        try {
            $items = [];

            if (false !== $is_static) {
                $constantFilter = new ConstantFilter([ConstantFilter::CLASS_NAME => $class_name], $pattern);
                $constants = $this->constantInfoRepository->find($constantFilter);

                foreach ($constants as $constant) {
                        $items[] = $this->view->renderConstantInfo($constant);
                }
            }

            $methodFilter = new MethodFilter([
                MethodFilter::CLASS_NAME    => $class_name,
                MethodFilter::PUBLIC_ONLY   => $public_only,
                MethodFilter::STATIC_ONLY   => $is_static,
            ], $pattern);

            $methods = $this->methodInfoRepository->find($methodFilter);

            foreach ($methods as $method) {
                $items[] = $this->view->renderMethodInfo($method);
            }

            $propertyFilter = new PropertyFilter([
                PropertyFilter::CLASS_NAME    => $class_name,
                PropertyFilter::PUBLIC_ONLY   => $public_only,
                PropertyFilter::STATIC_ONLY   => $is_static,
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

    public function getFixForNewClassUsage($path, array $new_class_params)
    {
        $info = $this->file_info_factory->createFileInfo($path);

        return $info->getFixForNewClassUsage($new_class_params);
    }

    public function getFunctionsAndConstants($pattern)
    {
        $items = [];
        $funcs = get_defined_functions();
        $funcs = array_merge($funcs['internal'], $funcs['user']);
        foreach ($funcs as $func) {
            $info = $this->getFunctionInfo($func, $pattern);
            if ($info) {
                $items[] = $info;
            }
        }

        return array_merge($items, $this->getConstantsInfo($pattern));
    }

    private function getConstantsInfo($pattern)
    {
        $items = [];
        foreach (get_defined_constants() as $name => $value) {
            if ($pattern && strpos($name, $pattern) !== 0) {
                continue;
            }

            $constantInfo = new ConstantInfo($name, $value);

            $items[] = $this->view->renderConstantInfo($constantInfo);
        }

        return $items;
    }

    private function getFunctionInfo($name, $pattern = null)
    {
        if ($pattern && strpos($name, $pattern) !== 0) {
            return null;
        }

        $functionInfo = new FunctionInfo(new \ReflectionFunction($name));

        return $this->view->renderFunctionInfo($functionInfo);
    }

    /**
     * generate psr4 namespace according composer.json and file path
     */
    public function psr4ns($path)
    {
        return $this->nsinfo->getByPath($path);
    }
}
