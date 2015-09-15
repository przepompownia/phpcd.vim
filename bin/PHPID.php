<?php
require __DIR__ . '/PHPCD.php';

class PHPID extends PHPCD
{
    private $index_dir;

    public function __construct($socket_path, $autoload_path, $map_file, $root)
    {
        $this->class_map = require $map_file;
        $this->root = $root;

        parent::__construct($socket_path, $autoload_path);
    }
    protected function setChannelId()
    {
    }

    protected function setChannelId0()
    {
        $command = 'let g:phpid_channel_id = ' . $this->getChannelId();
        $this->callRpc('vim_command',  $command);
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
        $extends_dir = $this->getExtendsDir();
        if (!is_dir($extends_dir)) {
            mkdir($extends_dir, 0700, true);
        }

        $interfaces_dir = $this->getIntefacesDir();
        if (!is_dir($interfaces_dir)) {
            mkdir($interfaces_dir, 0700, true);
        }
    }

    /**
     * 解析 autoload_classmap.php 生成类的继承关系和接口的实现关系
     */
    public function index($is_force = null)
    {
        if (is_dir($this->getIndexDir()) && !$is_force) {
            return;
        }

        $this->initIndexDir();

        exec('cd ' . $this->root . ' && composer dump-autoload --optimize');

        $pipe_path = sys_get_temp_dir() . '/' . uniqid();
        posix_mkfifo($pipe_path, 0600);

        $class_num = count($this->class_map);
        $cmd = 'let g:pb = vim#widgets#progressbar#NewSimpleProgressBar("Indexing:", ' . $class_num . ')';
        $this->callRpc('vim_command',  $cmd);

        while ($this->class_map) {
            $pid = pcntl_fork();

            if ($pid == -1) {
                die('could not fork');
            } elseif ($pid > 0) {
                // 父进程
                $pipe = fopen($pipe_path, 'r');
                $data = fgets($pipe);
                $this->class_map = json_decode(trim($data), true);
                pcntl_waitpid($pid, $status);
            } else {
                // 子进程
                $pipe = fopen($pipe_path, 'w');
                register_shutdown_function(function () use ($pipe) {
                    $data = json_encode($this->class_map, true);
                    fwrite($pipe, "$data\n");
                    fclose($pipe);
                });
                $this->_index();
                fwrite($pipe, "[]\n");
                fclose($pipe);
                exit;
            }
        }
        fclose($pipe);
        unlink($pipe_path);
        $this->callRpc('vim_command',  'call g:pb.restore()');
    }

    private function _index()
    {
        foreach ($this->class_map as $class_name => $file_path) {
            // TODO 为什么不是先处理再删除呢？
            unset($this->class_map[$class_name]);
            $this->callRpc('vim_command',  'call g:pb.incr()');
            require $file_path;
            list($parent, $interfaces) = $this->getClassInfo($class_name);
            if ($parent) {
                $this->updateParentIndex($parent, $class_name);
            }
            foreach ($interfaces as $interface) {
                $this->updateInterfaceIndex($interface, $class_name);
            }
        }
    }

    public function loop()
    {
        $this->index();
        // 索引完成方可使用
        $this->setChannelId0();

        parent::loop();
    }

    private function updateParentIndex($parent, $child)
    {
        $index_file = $this->getExtendsDir() . '/' . $this->getIndexFileName($parent);
        if (is_file($index_file)) {
            $childs = json_decode(file_get_contents($index_file));
        } else {
            $childs = [];
        }

        $childs[] = $child;
        file_put_contents($index_file, json_encode($childs));
    }

    private function updateInterfaceIndex($interface, $implementation)
    {
        $index_file = $this->getIntefacesDir() . '/' . $this->getIndexFileName($interface);
        if (is_file($index_file)) {
            $childs = json_decode(file_get_contents($index_file));
        } else {
            $childs = [];
        }

        $childs[] = $implementation;
        file_put_contents($index_file, json_encode($childs));
    }

    private function getIndexFileName($name)
    {
        return str_replace("\\", '_', $name);
    }

    private function getClassInfo($name) {
        try {
            $reflection = new ReflectionClass($name);

            $parent = $reflection->getParentClass();
            if ($parent) {
                $parent = $parent->getName();
            }

            $interfaces = array_keys($reflection->getInterfaces());

            return [$parent, $interfaces];
        } catch (ReflectionException $e) {
            return [null, []];
        }
    }

    /**
     * 更新特定类的继承关系和接口实现关系
     * @param string $class_name 全限定类名
     */
    private function update($class_name)
    {
    }

    public function ls($name, $is_interface = false)
    {
        $base_path = $is_interface ? $this->getIntefacesDir() : $this->getExtendsDir();
        $path = $base_path . '/' . $this->getIndexFileName($name);
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
}
