<?php
error_reporting(0);

$root   = $argv[1];
$daemon = $argv[2];

/** load autoloader for PHPCD **/
require __DIR__ . '/../vendor/autoload.php';
/** load autoloader for the project **/
require $root . '/vendor/autoload.php';

$factory = new \PHPCD\Factory;

$log_path = getenv('HOME') . '/.phpcd.log';
$logger = new PHPCD\Logger($log_path);

try {
    $unpacker = $factory->createMessageUnpacker();

    $daemon = $factory->createDaemon($daemon_name, $root, $unpacker, $logger);

    $daemon->loop();
} catch (\Throwable $e) {
    $logger->error($e->getMessage(), $e->getTrace());
} catch (\Exception $e) {
    $logger->error($e->getMessage(), $e->getTrace());
}
