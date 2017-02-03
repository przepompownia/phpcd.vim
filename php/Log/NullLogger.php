<?php

namespace PHPCD\Log;

use Psr\Log\AbstractLogger;

class NullLogger extends AbstractLogger
{
    /**
     * Does nothing.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = [])
    {
        return null;
    }
}
