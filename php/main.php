<?php
error_reporting(0);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
$root = $argv[1];
$daemon_name = $argv[2];
$input_options = json_decode($argv[3], true) ?: [];

/** @todo: update documentation about config variables **/

$default_options = [
    'logger' => [
        'implementation'    => '\\Monolog\\Logger',
        'parameters'        => []
    ],
    'completion' => [
        'match_type' => 'head',
        'case_sensitivity' => 0
    ]
];

$options = $default_options;

foreach ($default_options as $option => $default_values) {
    if (isset($input_options[$option])) {
        if (is_array($input_options[$option]) && is_array($default_options[$option])) {
            $options[$option] = $input_options[$option] + $default_options[$option];
        }

        if (is_string($input_options[$option]) && is_string($default_options[$option])) {
            $options[$option] = $input_options[$option];
        }
    }
}

/** load autoloader for PHPCD **/
require __DIR__ . '/../vendor/autoload.php';

use Lvht\MsgpackRpc\ForkServer;
use Lvht\MsgpackRpc\DefaultMsgpacker;
use Lvht\MsgpackRpc\StdIo;

$factory = new \PHPCD\Factory;

/** Instantiate daemon's logger **/
$logger = $factory->createLogger(
    $options['logger']['implementation'],
    $options['logger']['parameters']
);

try {
    /** load autoloader for the project **/
    require $root . '/vendor/autoload.php';

    $handler = $factory->createRpcHandler($daemon_name, $root, $logger, $options);

    $packer = $factory->createMsgpacker();
    $io  = $factory->createIo();
    $server = $factory->createServer($packer, $io, $handler);

    if ($daemon_name == 'PHPID') {
        $handler->index();
    }

    $server->loop();
} catch (\Throwable $e) {
    $logger->error($e->getMessage(), $e->getTrace());
} catch (\Exception $e) {
    $logger->error($e->getMessage(), $e->getTrace());
}
