<?php

namespace PHPCD;

use Lvht\MsgpackRpc\Handler as RpcHandler;
use Lvht\MsgpackRpc\Server;
use Lvht\MsgpackRpc\ForkServer;
use Lvht\MsgpackRpc\Msgpacker;
use Lvht\MsgpackRpc\DefaultMsgpacker;
use Lvht\MsgpackRpc\Io;
use Lvht\MsgpackRpc\StdIo;

/**
 * Simple factory to separate details of object creation
 */
class Factory
{
    /**
     * @param string $implementation absolute namespace path to a concrete logger
     * @param array $parameters parameters to logger's constructor
     * @return \Psr\Log\LoggerInterface
     */
    public function createLogger($implementation, $parameters = [])
    {
        switch ($implementation) {
            case '\\PHPCD\\Log\\DateTimeLogger':
                $decoratedLogger = $this->createLogger('\\PHPCD\\Log\\Logger', $parameters);
                return new Log\DateTimeLogger($decoratedLogger);
            break;
            case '\\PHPCD\\Log\\NullLogger':
                return new Log\NullLogger;
            break;
            case '\\Monolog\\Logger':
                $path = ((isset($parameters[0]) && is_string($parameters[0])) ? $parameters[0] : getenv('HOME') . '/.phpcd.log');

                $logger = new \Monolog\Logger('PHPCD');
                $logger->pushHandler(new \Monolog\Handler\StreamHandler($path, \Monolog\Logger::DEBUG));
                return $logger;
            break;
            case '\\PHPCD\\Log\\Logger':
            default:
                $path = null;

                if (isset($parameters[0])) {
                    $path = $parameters[0];
                }

                return new Log\Logger($path);
            break;
        }
    }

    /**
     * @return RpcHandler
     */
    public function createRpcHandler(
        $daemon_name,
        $root,
        $logger,
        $options
    ) {
        $pattern_matcher = $this->createPatternMatcher(
            $options['completion']['match_type'],
            $options['completion']['case_sensitivity']
        );

        switch ($daemon_name) {
            case 'PHPCD':
                $file_info_factory = new \PHPCD\PHPFileInfo\PHPFileInfoFactory;

                return new PHPCD($root, $logger, $pattern_matcher, $file_info_factory);
            case 'PHPID':
                $class_info_factory = new \PHPCD\ClassInfo\ClassInfoFactory;

                $clases_repository = $this->createClassInfoRepository(
                    $root,
                    $pattern_matcher,
                    $class_info_factory,
                    $logger
                );

                return new PHPID($root, $logger, $clases_repository);
            default:
                throw new \InvalidArgumentException('The daemon name should be PHPCD or PHPID');
        }
    }

    /**
     * @return Msgpacker
     */
    public function createMsgpacker()
    {
        return new DefaultMsgpacker;
    }

    /**
     * @return Io
     */
    public function createIo()
    {
        return new StdIo;
    }
    /**
     * @return \PHPCD\PatternMatcher\PatternMatcher
     */
    public function createPatternMatcher($match_type = 'head', $case_sensitivity = null)
    {
        $case_sensitivity = (bool)$case_sensitivity;

        if ($match_type === 'subsequence') {
            return new \PHPCD\PatternMatcher\SubsequencePatternMatcher($case_sensitivity);
        }

        return new \PHPCD\PatternMatcher\HeadPatternMatcher($case_sensitivity);
    }

    /**
     * @return \PHPCD\ClassInfo\ClassInfo
     */
    public function createClassInfoRepository($root, $pattern_matcher, $classInfoFactory, $logger)
    {
        return new \PHPCD\ClassInfo\ComposerClassmapFileRepository($root, $pattern_matcher, $classInfoFactory, $logger);
    }
    /**
     * @return Server
     */
    public function createServer(Msgpacker $packer, Io $io, RpcHandler $handler)
    {
        return new ForkServer($packer, $io, $handler);
    }
}
