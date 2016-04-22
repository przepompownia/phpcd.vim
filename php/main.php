<?php
error_reporting(0);
$root = $argv[1];
$daemon_name = $argv[2];

/** @todo: update documentation about config variables **/

$default_options = [
    'completion' => [
        'match_type' => 'head',
        'case_sensitivity' => 0
    ]
];

$options = $input_options + $default_options;

/** load autoloader for PHPCD **/
require __DIR__ . '/../vendor/autoload.php';
/** load autoloader for the project **/
require $root . '/vendor/autoload.php';

$factory = new \PHPCD\Factory;

$log_path = getenv('HOME') . '/.phpcd.log';
$logger = new PHPCD\Logger($log_path);

try {
    $unpacker = $factory->createMessageUnpacker();

    $pattern_matcher = $factory->createPatternMatcher(
        $options['completion']['match_type'],
        $options['completion']['case_sensitivity']
    );

    $daemon = $factory->createDaemon($daemon_name, $root, $unpacker, $pattern_matcher, $logger);

    $daemon->loop();
} catch (\Throwable $e) {
    $logger->error($e->getMessage(), $e->getTrace());
} catch (\Exception $e) {
    $logger->error($e->getMessage(), $e->getTrace());
}
