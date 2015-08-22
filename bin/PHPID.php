<?php
require __DIR__ . '/PHPCD.php';

class PHPID extends PHPCD
{
    private $index_dir;

    public function __construct($socket_path, $autoload_path = null, $map_file)
    {
        $this->map_file = $map_file;

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

    private function initIndexDir()
    {
        $this->index_dir = '/tmp/phpid/' . getmypid();
        $this->extends_dir = $this->index_dir . '/extends';
        if (!is_dir($this->extends_dir)) {
            mkdir($this->extends_dir, 0700, true);
        }

        $this->interfaces_dir = $this->index_dir . '/interfaces';
        if (!is_dir($this->interfaces_dir)) {
            mkdir($this->interfaces_dir, 0700, true);
        }
    }

    /**
     * 解析 autoload_classmap.php 生成类的继承关系和接口的实现关系
     */
    public function index()
    {
        $map = require "$this->map_file";
        $this->initIndexDir();

        $ct = time();
        foreach ($map as $class_name => $file_path) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                die('could not fork');
            } elseif ($pid) {
                pcntl_wait($status);
            } else {
                list($parent, $interfaces) = $this->getClassInfo($class_name);
                if ($parent) {
                    $this->updateParentIndex($parent, $class_name);
                }
                foreach ($interfaces as $interface) {
                    $this->updateInterfaceIndex($interface, $class_name);
                }
                exit;
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
        $index_file = $this->extends_dir . '/' . $this->getIndexFileName($parent);
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
        $index_file = $this->interfaces_dir . '/' . $this->getIndexFileName($interface);
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
}
