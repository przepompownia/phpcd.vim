<?php

namespace PHPCD;

use Lvht\MsgpackRpc\Handler as RpcHandler;
use Lvht\MsgpackRpc\Server as RpcServer;
use PHPCD\Element\ClassInfo\ClassInfoCollection;
use PHPCD\Element\ClassInfo\ClassInfoRepository;
use PHPCD\Filter\ClassFilter;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface as Logger;

class PHPID implements RpcHandler
{
    use LoggerAwareTrait;

    /**
     * @var RpcServer
     */
    private $server;

    private $root;

    private $classMap;

    /**
     * @var ClassInfoRepository
     */
    private $classesRepository;

    public function __construct(
        $root,
        Logger $logger,
        ClassInfoRepository $classesRepository
    ) {
        $this->setRoot($root);
        $this->setLogger($logger);
        $this->setClassInfoRepository($classesRepository);
    }

    /**
     * Set the composer root dir
     *
     * @param string $root the path
     * @return static
     */
    private function setRoot($root)
    {
        // @TODO do we need to validate this input variable?
        $this->root = $root;
        return $this;
    }

    public function setServer(RpcServer $server)
    {
        $this->server = $server;
    }

    protected function setClassInfoRepository(ClassInfoRepository $classesRepository)
    {
        $this->classesRepository = $classesRepository;
        return $this;
    }

    /**
     * update index for one class
     *
     * @param string $className fqdn
     */
    public function update($className)
    {
        list($parent, $interfaces) = $this->getClassInfo($className);

        if ($parent) {
            $this->updateParentIndex($parent, $className);
        }
        foreach ($interfaces as $interface) {
            $this->updateInterfaceIndex($interface, $className);
        }
    }

    /**
     * Fetch an interface's implementation list,
     * or an abstract class's child class.
     *
     * @param string $name name of interface or abstract class
     * @param bool $isAbstractClass
     *
     * @return array array of FQCNs
     */
    public function ls($name, $isAbstractClass = false)
    {
        $basePath = $isAbstractClass ? $this->getIntefacesDir()
            : $this->getExtendsDir();
        $path = $basePath . '/' . $this->getIndexFileName($name);
        if (!is_file($path)) {
            return [];
        }

        $list = json_decode(file_get_contents($path));
        if (!is_array($list)) {
            return [];
        }

        sort($list);

        return $list;
    }

    /**
     * Fetch and save class's interface and parent info
     * according the autoload_classmap.php file
     */
    public function index()
    {
        $this->initIndexDir();

        exec('composer dump-autoload -o -d ' . $this->root . ' 2>&1 >/dev/null');

        $this->classMap = require $this->root
            . '/vendor/composer/autoload_classmap.php';

        $pipePath = sys_get_temp_dir() . '/' . uniqid();
        posix_mkfifo($pipePath, 0600);

        $this->vimOpenProgressBar(count($this->classMap));

        while ($this->classMap) {
            $pid = pcntl_fork();

            if ($pid == -1) {
                die('could not fork');
            } elseif ($pid > 0) {
                // 父进程
                $pipe = fopen($pipePath, 'r');
                $data = fgets($pipe);
                $this->classMap = json_decode(trim($data), true);
                pcntl_waitpid($pid, $status);
            } else {
                // 子进程
                $pipe = fopen($pipePath, 'w');
                register_shutdown_function(function () use ($pipe) {
                    $data = json_encode($this->classMap, true);
                    fwrite($pipe, "$data\n");
                    fclose($pipe);
                });
                $this->_index();
                fwrite($pipe, "[]\n");
                fclose($pipe);
                exit;
            }
        }

        if (isset($pipe) && is_resource($pipe)) {
            fclose($pipe);
        }

        unlink($pipePath);
        $this->vimCloseProgressBar();
    }

    private function getIndexDir()
    {
        return $this->root . '/.phpcd';
    }

    private function getIntefacesDir()
    {
        return $this->getIndexDir() . '/interfaces';
    }

    private function getExtendsDir()
    {
        return $this->getIndexDir() . '/extends';
    }

    private function initIndexDir()
    {
        $extendsDir = $this->getExtendsDir();
        if (!is_dir($extendsDir)) {
            mkdir($extendsDir, 0700, true);
        }

        $interfacesDir = $this->getIntefacesDir();
        if (!is_dir($interfacesDir)) {
            mkdir($interfacesDir, 0700, true);
        }
    }

    private function _index()
    {
        foreach ($this->classMap as $className => $filePath) {
            unset($this->classMap[$className]);
            $this->vimUpdateProgressBar();
            require $filePath;
            $this->update($className);
        }
    }

    private function updateParentIndex($parent, $child)
    {
        $indexFile = $this->getExtendsDir() . '/' . $this->getIndexFileName($parent);
        $this->saveChild($indexFile, $child);
    }

    private function updateInterfaceIndex($interface, $implementation)
    {
        $indexFile = $this->getIntefacesDir() . '/' . $this->getIndexFileName($interface);
        $this->saveChild($indexFile, $implementation);
    }

    private function saveChild($indexFile, $child)
    {
        $indexDirectory = dirname($indexFile);

        if (!is_dir($indexDirectory)) {
            mkdir($indexDirectory, 0755, true);
        }

        if (is_file($indexFile)) {
            $childs = json_decode(file_get_contents($indexFile));
        } else {
            $childs = [];
        }

        $childs[] = $child;
        $childs = array_unique($childs);
        file_put_contents($indexFile, json_encode($childs));
    }

    private function getIndexFileName($name)
    {
        return str_replace("\\", '_', $name);
    }

    private function getClassInfo($name)
    {
        try {
            $reflection = new \ReflectionClass($name);

            $parent = $reflection->getParentClass();
            if ($parent) {
                $parent = $parent->getName();
            }

            $interfaces = array_keys($reflection->getInterfaces());

            return [$parent, $interfaces];
        } catch (\ReflectionException $e) {
            return [null, []];
        }
    }

    private function vimOpenProgressBar($max)
    {
        $cmd = 'let g:pb = vim#widgets#progressbar#NewSimpleProgressBar("Indexing:", ' . $max . ')';
        $this->server->call('vim_command', [$cmd]);
    }

    private function vimUpdateProgressBar()
    {
        $this->server->call('vim_command', ['call g:pb.incr()']);
    }

    private function vimCloseProgressBar()
    {
        $this->server->call('vim_command', ['call g:pb.restore()']);
    }

    public function getAbsoluteClassesPaths($pathPattern)
    {
        $filter = new ClassFilter([], $pathPattern);

        $collection = $this->classesRepository->find($filter);

        return $this->prepareOutputFromClassInfoCollection($collection, false);
    }

    public function getInterfaces($pathPattern)
    {
        $filter = new ClassFilter([ClassFilter::IS_INTERFACE => true], $pathPattern);

        $collection = $this->classesRepository->find($filter);

        return $this->prepareOutputFromClassInfoCollection($collection, true);
    }

    public function getPotentialSuperclasses($pathPattern)
    {
        $filter = new ClassFilter([
            ClassFilter::IS_FINAL => false,
            ClassFilter::IS_TRAIT => false,
            ClassFilter::IS_INTERFACE => false
        ], $pathPattern);

        $collection = $this->classesRepository->find($filter);

        return $this->prepareOutputFromClassInfoCollection($collection, true);
    }

    public function getInstantiableClasses($pathPattern)
    {
        $filter = new ClassFilter([ClassFilter::IS_INSTANTIABLE => true], $pathPattern);

        $collection = $this->classesRepository->find($filter);

        return $this->prepareOutputFromClassInfoCollection($collection, true);
    }

    public function getNamesToTypeDeclaration($pathPattern)
    {
        // @TODO add basic types
        $filter = new ClassFilter([ClassFilter::IS_TRAIT => false], $pathPattern);

        $collection = $this->classesRepository->find($filter);

        return $this->prepareOutputFromClassInfoCollection($collection, true);
    }

    /**
     * Prepare single element of completion output
     * @param ClassInfoCollection $collection
     * @param bool $leadingBackslash prepend class path with backslash
     * @return array
     */
    private function prepareOutputFromClassInfoCollection(
        ClassInfoCollection $collection,
        $leadingBackslash = true
    ) {
        $result = [];

        foreach ($collection as $classInfo) {
            $result[] = [
                'full_name' => ($leadingBackslash ? '\\' : '') . $classInfo->getName(),
                'short_name' => $classInfo->getShortName(),
                'doc_comment' => $classInfo->getDocComment()
            ];
        }

        return $result;
    }

    public function locateClassDeclaration($className)
    {
        try {
            $class = $this->classesRepository->get($className);

            return [$class->getFileName(), $class->getStartLine()];
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage(), $e->getTrace());

            return ['', null];
        }
    }
}
