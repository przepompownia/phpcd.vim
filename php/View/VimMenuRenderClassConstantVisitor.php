<?php

namespace PHPCD\View;

use PHPCD\Element\ConstantInfo\ClassConstantInfo;

class VimMenuRenderClassConstantVisitor extends VimMenuRenderAbstractVisitor implements ClassConstantVisitor
{
    public function visitElement(ClassConstantInfo $constantInfo)
    {
        $out = new VimMenuItem();
        $out->setWord($constantInfo->getName());
        $out->setAbbr(sprintf(' +@ %s %s', $constantInfo->getName(), $constantInfo->getValue()));
        $out->setKind('d');

        $this->output[] = $out->render();
    }
}
