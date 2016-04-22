<?php

namespace PHPCD;

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
    public function createLogger($implementation, $parameters)
    {
        switch ($implementation) {
            case '\\PHPCD\\Logger':
            default:
                return new Logger($parameters[0]);
            break;
        }
    }

    /**
     * @return PHPCD|PHPID
     */
    public function createDaemon($daemon_name, $root, $unpacker, $logger)
    {
        switch ($daemon_name) {
            case 'PHPCD':
            case 'PHPID':
                break;
            default:
                throw new \InvalidArgumentException('The daemon name should be PHPCD or PHPID');
        }

        /** relative class path did used in variable was not recognized **/
        $daemon_name = __NAMESPACE__.'\\'.$daemon_name;

        $daemon = new $daemon_name($root, $unpacker, $logger);

        return $daemon;
    }

    /**
     * @return \MessagePackUnpacker
     */
    public function createMessageUnpacker()
    {
        return new \MessagePackUnpacker;
    }
    /**
     * @return PHPCD\PatternMatcher\PatternMatcher
     */
    public function createPatternMatcher($match_type = 'head', $case_sensitivity = null)
    {
        $case_sensitivity = (bool)$case_sensitivity;

        if ($match_type === 'subsequence') {
            return new \PHPCD\PatternMatcher\SubsequencePatternMatcher($case_sensitivity);
        }

        return new \PHPCD\PatternMatcher\HeadPatternMatcher($case_sensitivity);
    }
}
