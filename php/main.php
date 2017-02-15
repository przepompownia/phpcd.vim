<?php

error_reporting(-1);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$root = $argv[1];
$parameters = (empty($argv[2]) ? '' : $argv[2]);
$parameters = json_decode($parameters, true) ?: [];
$parameters['root'] = realpath($root);

/** load autoloader for the project **/
$composerAutoloadFile = $root.'/vendor/autoload.php';
$projectAutoloadFile = empty($parameters['autoload_file']) ? $composerAutoloadFile : $parameters['autoload_file'];
if (!is_readable($projectAutoloadFile)) {
    // @TODO non-composer class loader
    throw new Exception('This version still depends of autoloader from composer.');
}
$projectClassLoader = require $projectAutoloadFile;
$parameters['class_loader'] = $projectClassLoader;

/** load autoloader for PHPCD **/
$phpcdAutoloadFile = __DIR__.'/../vendor/autoload.php';
/** @var \Composer\Autoload\ClassLoader $phpcdClassLoader */
$phpcdClassLoader = require $phpcdAutoloadFile;

\PHPCD\WhiteList::load();

$factory = new \PHPCD\Factory();

$configDir = __DIR__.'/../config/';
$dIContainer = $factory->createDIContainer('services.yml', $configDir, $parameters);

$logger = $dIContainer->get('default_logger');

try {
    $server = $dIContainer->get('server.phpcd');
    $server->addHandler($dIContainer->get('handler.phpid'));
    $server->loop();
} catch (\Throwable $e) {
    $logger->error($e->getMessage(), $e->getTrace());
} catch (\Exception $e) {
    $logger->error($e->getMessage(), $e->getTrace());
}
