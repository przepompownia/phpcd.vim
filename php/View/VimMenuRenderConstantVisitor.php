<?php

namespace PHPCD\View;

use PHPCD\Element\ConstantInfo\ConstantInfo;

class VimMenuRenderConstantVisitor extends VimMenuRenderAbstractVisitor implements ConstantVisitor
{
    public function visitElement(ConstantInfo $constantInfo)
    {
        $out = new VimMenuItem();
        $out->setWord($constantInfo->getName());

        $value = $constantInfo->getValue();
        if (is_array($value)) {
            $value = sprintf('[%s, ...]', implode(',', array_slice($value, 0, 2, false)));
        }
        $out->setAbbr(sprintf(' +@ %s %s', $constantInfo->getName(), $value));
        $out->setKind('d');

        $this->output[] = $out->render();
    }
}
