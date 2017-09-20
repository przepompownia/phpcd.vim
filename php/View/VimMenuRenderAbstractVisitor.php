<?php

namespace PHPCD\View;

use PHPCD\Element\CollectionVisitor;

abstract class VimMenuRenderAbstractVisitor implements CollectionVisitor
{
    protected $output = [];

    public function getOutput()
    {
        return $this->output;
    }
}
