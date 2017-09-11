<?php

namespace PHPCD\Element;

class PhysicalLocation
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * @var int
     */
    private $lineNumber;

    public function __construct($fileName, $lineNumber)
    {
        $this->fileName = $fileName;
        $this->lineNumber = $lineNumber;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getLineNumber()
    {
        return $this->lineNumber;
    }
}
