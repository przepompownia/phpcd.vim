<?php
class PHPCD
{
    private static $req_msg_id = 0;

    /**
     * 套接字
     */
    private $socket;

    /** @var MessagePackUnpacker $unpacker **/
    private $unpacker;

    /**
     * @param string $socket_path 套接字路径
     * @param string $autoload_path PHP 项目自动加载脚本
     */
    public function __construct($socket_path, $autoload_path = null)
    {
        $this->socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
        $this->unpacker = new MessagePackUnpacker();

        if ($autoload_path) {
            require $autoload_path;
        }

        socket_connect($this->socket, $socket_path);

        $this->setChannelId();
    }

    protected function getChannelId()
    {
        list($result, $error) = $this->callRpc('vim_get_api_info');
        return $result[0];
    }

    protected function setChannelId()
    {
        $command = 'let g:phpcd_channel_id = ' . $this->getChannelId();
        $this->callRpc('vim_command',  $command);
    }

    /**
     * @return [$result, $error]
     */
    protected function callRpc()
    {
        $args = func_get_args();
        if (count($args) === 0) {
            throw new InvalidArgumentException('at least one args');
        }

        $method = array_shift($args);

        $req = msgpack_pack([
            0,
            self::$req_msg_id++,
            $method,
            $args
        ]);

        socket_send($this->socket, $req, strlen($req), 0);

        // TODO 默认发送调用请求之后会立即得到相应
        foreach ($this->nextRpcMsg() as $msg) {
            return [$msg[3], $msg[2]];
        }
    }
    private function nextRpcMsg()
    {
        while (socket_recv($this->socket, $buf, 1024, 0)) {
            $this->unpacker->feed($buf);

            while ($this->unpacker->execute()) {
                $unserialized = $this->unpacker->data();
                $this->unpacker->reset();
                yield $unserialized;
            }
        }
    }

    public function loop()
    {
        foreach ($this->nextRpcMsg() as $msg) {
            echo json_encode($msg) . PHP_EOL;
            $pid = pcntl_fork();
            if ($pid == -1) {
                die('could not fork');
            } elseif ($pid) {
                pcntl_wait($status);
            } else {
                $this->on($msg);
                exit;
            }
        }
    }

    private function on($msg)
    {
        var_dump($msg);
        $msg_id = null;
        if (count($msg) == 4) {
            // rpc request
            list($type, $msg_id, $method, $args) = $msg;
        } elseif (count($msg)) {
            // rpc notify
            list($type, $method, $args) = $msg;
        }

        $result = null;
        $error = null;
        try {
            $result = $this->onCall($method, $args);
        } catch (Exception $e) {
            $error = true;
        }

        $this->sendResp($result, $msg_id, $error);
    }

    private function sendResp($result, $msg_id = null, $error = null)
    {
        if ($msg_id) {
            $msg = msgpack_pack([
                1,
                $msg_id,
                null,
                $result,
            ]);
        } else {
            $msg = msgpack_pack([
                1,
                null,
                $result,
            ]);
        }

        socket_send($this->socket, $msg, strlen($msg), 0);
    }

    private function onCall($method, $args)
    {
        if (!method_exists($this, $method)) {
            return;
        }

        return call_user_func_array([$this, $method], $args);
    }

    public function info ($class_name, $pattern) {
        if ($class_name) {
            return $this->classInfo($class_name, $pattern);
        } else {
            return $this->functionOrConstantInfo($pattern);
        }
    }

    private function classInfo($class_name, $pattern)
    {
        $reflection = new ReflectionClass($class_name);
        $items = [];
        foreach ($reflection->getConstants() as $name => $value)
        {
            $items[] = [
                'word' => $name,
                'abbr' => "+ @ $name = $value",
                'kind' => 'd',
                'icase' => 1,
            ];
        }

        foreach ($reflection->getMethods() as $method) {
            $info = $this->getMethodInfo($method, $pattern);
            if ($info) {
                $items[] = $info;
            }
        }

        return $items;
    }

    private function functionOrConstantInfo($pattern)
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

        $reflection = new ReflectionFunction($name);
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

    private function getMethodInfo($method, $pattern = null)
    {
        $name = $method->getName();
        if ($pattern && strpos($name, $pattern) !== 0) {
            return null;
        }
        $params = array_map(function ($param) {
            return $param->getName();
        }, $method->getParameters());

        if ($method->isPublic()) {
            $modifier = '+';
        } elseif ($method->isProtected()) {
            $modifier = '#';
        } elseif ($method->isPrivate()) {
            $modifier = '-';
        } elseif ($method->isFinal()) {
            $modifier = '!';
        }

        $static = $method->isStatic() ? '@' : ' ';

        return [
            'word' => $name,
            'abbr' => "$modifier $static $name (" . join(', ', $params) . ')',
            'info' => preg_replace('#/?\*(\*|/)?#','', $method->getDocComment()),
            'kind' => 'f',
            'icase' => 1,
        ];
    }

    private function location($class_name, $method_name = null)
    {
        if ($class_name) {
            return $this->locationClass($class_name, $method_name);
        } else {
            return $this->locationFunction($method_name);
        }
    }

    private function locationClass($class_name, $method_name = null)
    {
        $class = new ReflectionClass($class_name);
        if (!$method_name) {
            return [
                $class->getFileName(),
                $class->getStartLine(),
            ];
        }

        $method  = $class->getMethod($method_name);

        return [
            $method->getFileName(),
            $method->getStartLine(),
        ];
    }

    private function locationFunction($name)
    {
        $func = new ReflectionFunction($name);
        return [
            $func->getFileName(),
            $func->getStartLine(),
        ];
    }
}
