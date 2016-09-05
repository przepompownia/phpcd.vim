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
        ConstantInfoRepository $constantInfoRepository,
        PropertyInfoRepository $propertyInfoRepository,
        MethodInfoRepository $methodInfoRepository,
        PHPFileInfoFactory $file_info_factory
    ) {
        $this->nsinfo = $nsinfo;
        $this->setLogger($logger);
        $this->file_info_factory = $file_info_factory;
        $this->constantInfoRepository = $constantInfoRepository;
        $this->propertyInfoRepository = $propertyInfoRepository;
        $this->methodInfoRepository = $methodInfoRepository;
    }

    public function setServer(RpcServer $server)
    {
        $this->server = $server;
    }

    /**
     *  @param array Map between modifier numbers and displayed symbols
     */
    private $modifier_symbols = [
       'final'     => '!',
       'private'    => '-',
       'protected'  => '#',
       'public'     => '+',
       'static'     => '@'
    ];

    /**
     * @param string $mode
     * @return bool|null
     */
    private function translateStaticMode($mode)
    {
        $map = [
            'both'           => null,
            'only_nonstatic' => false,
            'only_static'    => true
        ];

        return isset($map[$mode]) ? $map[$mode] : null;
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
    public function location($class_name, $method_name = null)
    {
        try {
            if ($class_name) {
                $reflection = new \ReflectionClass($class_name);
                if ($method_name) {
                    if ($reflection->hasMethod($method_name)) {
                        $reflection = $reflection->getMethod($method_name);
                    } elseif ($reflection->hasConstant($method_name)) {
                        // 常量则返回 [ path, 'const CONST_NAME' ]
                        return [$this->getConstPath($method_name, $reflection), 'const ' . $method_name];
                    }
                }
            } else {
                $reflection = new \ReflectionFunction($method_name);
            }

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
                $this->logger->debug('hehe2');
                $reflection = new \ReflectionClass($class_name);
                if (!$is_method) {
                    $reflection_class = $reflection;
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

            $path = $reflection_class ? $reflection_class->getFileName()
                : $reflection->getFileName();
            $doc = $reflection->getDocComment();

            return [$path, $this->clearDoc($doc)];
        } catch (\ReflectionException $e) {
            $this->logger->debug((string) $e);
            return [null, null];
        }
    }

    /**
     * Fetch the php script's namespace and imports(by use) list.
     *
     * @param string $path the php script path
     * @param string $specific_alias in not empty, do not get information about other aliases
     *
     * @return [
     *   'namespace' => 'ns',
     *   'class' => 'shortname'
     *   'imports' => [
     *     'alias1' => 'fqdn1',
     *   ]
     * ]
     */
    public function nsuse($path)
    {
        $file_info = $this->file_info_factory->createFileInfo($path);
        return [
            'namespace' => $file_info->getNamespace(),
            'class' => $file_info->getClass(),
            'imports' => $file_info->getImports()
        ];
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
        return $this->typeByDoc($path, $doc);
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

    private function typeByDoc($path, $doc) {
        $has_doc = preg_match('/@(return|var)\s+(\S+)/m', $doc, $matches);
        if (!$has_doc) {
            return [];
        }

        $nsuse = $this->nsuse($path);

        $types = [];
        foreach (explode('|', $matches[2]) as $type) {
            if (isset($this->primitive_types[$type])) {
                continue;
            }

            if (in_array(strtolower($type) , ['static', '$this', 'self'])) {
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

    public function getMatchingClassDetails($class_name, $pattern, $is_static = 'both', $public_only = true)
    {
        $is_static = $this->translateStaticMode($is_static);

        try {
            $items = [];

            if (false !== $is_static) {
                $constantFilter = new ConstantFilter([ConstantFilter::CLASS_NAME => $class_name], $pattern);
                $constants = $this->constantInfoRepository->find($constantFilter);

                foreach ($constants as $constant) {
                        $items[] = [
                            'word' => $constant->getName(),
                            'abbr' => sprintf(" +@ %s %s", $constant->getName(), $constant->getValue()),
                            'kind' => 'd',
                            'icase' => 1,
                        ];
                }
            }

            $methodFilter = new MethodFilter([
                MethodFilter::CLASS_NAME    => $class_name,
                MethodFilter::PUBLIC_ONLY   => $public_only,
                MethodFilter::STATIC_ONLY   => $is_static,
            ], $pattern);

            $methods = $this->methodInfoRepository->find($methodFilter);

            foreach ($methods as $method) {
                $items[] = $this->getMethodInfo($method, $pattern);
            }

            $propertyFilter = new PropertyFilter([
                PropertyFilter::CLASS_NAME    => $class_name,
                PropertyFilter::PUBLIC_ONLY   => $public_only,
                PropertyFilter::STATIC_ONLY   => $is_static,
            ], $pattern);

            $properties = $this->propertyInfoRepository->find($propertyFilter);

            foreach ($properties as $property) {
                $items[] = $this->getPropertyInfo($property);
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
        foreach ($funcs['internal'] as $func) {
            $info = $this->getFunctionInfo($func, $pattern);
            if ($info) {
                $items[] = $info;
            }
        }
        foreach ($funcs['user'] as $func) {
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

            $items[] = [
                'word' => $name,
                'abbr' => "@ $name = $value",
                'kind' => 'd',
                'icase' => 0,
            ];
        }

        return $items;
    }

    private function getFunctionInfo($name, $pattern = null)
    {
        if ($pattern && strpos($name, $pattern) !== 0) {
            return null;
        }

        $reflection = new \ReflectionFunction($name);
        $params = array_map(function ($param) {
            return $param->getName();
        }, $reflection->getParameters());

        return [
            'word' => $name,
            'abbr' => "$name(" . join(', ', $params) . ')',
            'info' => preg_replace('#/?\*(\*|/)?#','', $reflection->getDocComment()),
            'kind' => 'f',
            'icase' => 1,
        ];
    }

    private function getPropertyInfo($property)
    {
        $modifier = $this->getModifiers($property);

        return [
            'word' => $property->getName(),
            'abbr' => sprintf("%3s %s", $modifier, $property->getName()),
            'info' => preg_replace('#/?\*(\*|/)?#', '', $property->getDocComment()),
            'kind' => 'p',
            'icase' => 1,
        ];
    }

    private function getMethodInfo($method)
    {
        $params = array_map(function ($param) {
            return $param->getName();
        }, $method->getParameters());

        return [
            'word' => $method->getName(),
            'abbr' => sprintf(
                "%3s %s (%s)",
                $this->getModifiers($method),
                $method->getName(),
                join(', ', $params)
            ),
            'info' => $this->clearDoc($method->getDocComment()),
            'kind' => 'f',
            'icase' => 1,
        ];
    }

    private function getModifiers($objectElement)
    {
        return implode('', array_intersect_key($this->modifier_symbols, array_flip($objectElement->getModifiers())));
    }

    private function clearDoc($doc)
    {
        $doc = preg_replace('/[ \t]*\* ?/m', '', $doc);
        return preg_replace('#\s*\/|/\s*#', '', $doc);
    }

    /**
     * generate psr4 namespace according composer.json and file path
     */
    public function psr4ns($path)
    {
        return $this->nsinfo->getByPath($path);
    }
}
