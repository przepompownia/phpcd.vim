<?php
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
$home_path = getenv('HOME');
$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen($home_path . '/.phpcd.log', 'wb');
$STDERR = fopen($home_path . '/.phpcd.log', 'wb');

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
        require '/home/lvht/code/agj/vendor/autoload.php';
        socket_connect($this->socket, $socket_path);

        $this->setChannelId();
    }

    private function getChannelId()
    {
        list($result, $error) = $this->callRpc('vim_get_api_info');
        return $result[0];
    }

    private function setChannelId()
    {
        $command = 'let g:phpcd_channel_id = ' . $this->getChannelId();
        $this->callRpc('vim_command',  $command);
    }

    /**
     * @return [$result, $error]
     */
    private function callRpc()
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
            echo json_encode(msg) . PHP_EOL;
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
            echo (string) $e;
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

    private function copy($name) {
        return $name;
    }

    private function info($class_name, $pattern)
    {
        $reflection = new ReflectionClass($class_name);
        $methods = [];
        foreach ($reflection->getMethods() as $method) {
            $info = $this->getMethodInfo($method, $pattern);
            if ($info) {
                $methods[] = $info;
            }
        }

        return $methods;
    }

    private function getMethodInfo($method, $pattern = null)
    {
        $name = $method->getName();
        if ($pattern && strpos($name, $pattern) === false) {
            return null;
        }
        $params = array_map(function ($param) {
            return $param->getName();
        }, $method->getParameters());

        return [
            'word' => $name,
            'abbr' => $name . '(' . join(', ', $params) . ')',
            'info' => preg_replace('#/?\*(\*|/)?#','', $method->getDocComment()),
            'kind' => 'f',
            'icase' => 1,
        ];
    }

    private function location($class_name, $method_name = null)
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
}

$socket_path = $argv[1];

$cd = new PHPCD($socket_path);
$cd->loop();
