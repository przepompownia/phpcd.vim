<?php

namespace PHPCD\View;

use PHPCD\Element\ObjectElement\PropertyInfo;

class VimMenuRenderPropertyVisitor extends VimMenuRenderAbstractVisitor implements PropertyVisitor
{
    public function visitElement(PropertyInfo $propertyInfo)
    {
        $modifiers = $this->getModifiers($propertyInfo);
        $out = new VimMenuItem();
        $out->setWord($propertyInfo->getName());
        $out->setAbbr(sprintf('%3s %s', $modifiers, $propertyInfo->getName()));
        $out->setKind('p');
        $out->setInfo(preg_replace('#/?\*(\*|/)?#', '', $propertyInfo->getDocComment()));

        $this->output[] = $out->render();
    }
}
