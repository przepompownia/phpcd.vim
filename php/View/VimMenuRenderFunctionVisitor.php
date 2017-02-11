<?php

namespace PHPCD\View;

use PHPCD\Element\FunctionInfo\FunctionInfo;

class VimMenuRenderFunctionVisitor extends VimMenuRenderAbstractVisitor implements FunctionVisitor
{
    public function visitElement(FunctionInfo $functionInfo)
    {
        $params = array_map(function ($param) {
            return $param->getName();
        }, $functionInfo->getParameters());

        $out = new VimMenuItem();
        $out->setWord($functionInfo->getName());
        $out->setAbbr(sprintf(
            '%s(%s)',
            $functionInfo->getName(),
            implode(', ', $params)
        ));
        $out->setKind('f');
        $out->setInfo(preg_replace('#/?\*(\*|/)?#', '', $functionInfo->getDocComment()));

        $this->output[] = $out->render();
    }
}
