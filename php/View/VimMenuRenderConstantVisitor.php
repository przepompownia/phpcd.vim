<?php

namespace PHPCD\View;

use PHPCD\Element\ConstantInfo\ConstantInfo;
use PHPCD\View\VimMenuItem;

class VimMenuRenderConstantVisitor extends VimMenuRenderAbstractVisitor implements ConstantInfoVisitor
{
    public function visitElement(ConstantInfo $constantInfo)
    {
        $out = new VimMenuItem();
        $out->setWord($constantInfo->getName());
        $out->setAbbr(sprintf(' +@ %s %s', $constantInfo->getName(), $constantInfo->getValue()));
        $out->setKind('d');

        $this->output[] = $out->render();
    }
}
