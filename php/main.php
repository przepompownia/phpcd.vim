<?php
error_reporting(0);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$root   = $argv[1];
$daemon = $argv[2];

/** load autoloader for PHPCD **/
require __DIR__ . '/../vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Lvht\MsgpackRpc\ForkServer;
use Lvht\MsgpackRpc\DefaultMsgpacker;
use Lvht\MsgpackRpc\StdIo;

$log_path = getenv('HOME') . '/.phpcd.log';
$logger = new Logger('PHPCD');
$logger->pushHandler(new StreamHandler($log_path, Logger::DEBUG));

try {
    /** load autoloader for the project **/
    require $root . '/vendor/autoload.php';

    switch ($daemon) {
        case 'PHPCD':
            $handler = new PHPCD\PHPCD($root, $logger);
            break;
        case 'PHPID':
            $handler = new PHPCD\PHPID($root, $logger);
            break;
        default:
            throw new \InvalidArgumentException('The second parameter should be PHPCD or PHPID');
    }

    $server = new ForkServer(new DefaultMsgpacker, new StdIo, $handler);
    if ($daemon == 'PHPID') {
        $handler->index();
    }

    $server->loop();
} catch (\Throwable $e) {
    $logger->error($e->getMessage(), $e->getTrace());
} catch (\Exception $e) {
    $logger->error($e->getMessage(), $e->getTrace());
}
