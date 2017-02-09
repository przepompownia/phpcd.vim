<?php

namespace PHPCD\View;

use PHPCD\Element\ClassInfo\ClassInfo;

class VimMenuRenderClassVisitor extends VimMenuRenderAbstractVisitor implements ClassVisitor
{
    public function visitElement(ClassInfo $classInfo)
    {
        $out = new VimMenuItem();
        $out->setWord($classInfo->getName());
        $out->setAbbr('');
        $out->setKind('');
        $out->setInfo('');

        $this->output[] = $out->render();
    }
}
