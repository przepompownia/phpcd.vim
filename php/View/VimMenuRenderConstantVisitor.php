<?php

namespace PHPCD\View;

use PHPCD\Element\ConstantInfo\ConstantInfo;

class VimMenuRenderConstantVisitor extends VimMenuRenderAbstractVisitor implements ConstantVisitor
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
