<?php
error_reporting(0);

$root   = $argv[1];
$daemon = $argv[2];

/** load autoloader for PHPCD **/
require __DIR__ . '/../vendor/autoload.php';
/** load autoloader for the project **/
require $root . '/vendor/autoload.php';

$log_path = getenv('HOME') . '/.phpcd.log';
$logger = new PHPCD\Logger($log_path);

try {
    switch ($daemon) {
        case 'PHPCD':
        case 'PHPID':
            break;
        default:
            throw new \InvalidArgumentException('The second parameter should be PHPCD or PHPID');
    }

    /** load autoloader for the project **/
    $projectClassLoader = new \PHPCD\ComposerClassmapFileRepository($root, $logger);

    $daemon = '\\PHPCD\\'.$daemon;
    $unpacker = new \MessagePackUnpacker;

    (new $daemon($root, $unpacker, $logger, $projectClassLoader))->loop();
} catch (\Throwable $e) {
    $logger->error($e->getMessage(), $e->getTrace());
} catch (\Exception $e) {
    $logger->error($e->getMessage(), $e->getTrace());
}
