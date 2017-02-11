<?php

namespace PHPCD\View;

use PHPCD\Element\ObjectElement\MethodInfo;

class VimMenuRenderMethodVisitor extends VimMenuRenderAbstractVisitor implements MethodVisitor
{
    private function clearDoc($doc)
    {
        $doc = preg_replace('/[ \t]*\* ?/m', '', $doc);

        return preg_replace('#\s*\/|/\s*#', '', $doc);
    }

    public function visitElement(MethodInfo $methodInfo)
    {
        $params = array_map(function ($param) {
            return $param->getName();
        }, $methodInfo->getParameters());

        $out = new VimMenuItem();
        $out->setWord($methodInfo->getName());
        $out->setAbbr(sprintf(
            '%3s %s (%s)',
            $this->getModifiers($methodInfo),
            $methodInfo->getName(),
            implode(', ', $params)
        ));
        $out->setKind('f');
        $out->setInfo($this->clearDoc($methodInfo->getDocComment()));

        $this->output[] = $out->render();
    }
}
