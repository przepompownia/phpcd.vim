<?php

namespace PHPCD\View;

use PHPCD\Element\ObjectElement\PropertyInfo;

class VimMenuRenderPropertyVisitor extends VimMenuRenderAbstractVisitor implements PropertyVisitor
{
    public function visitElement(PropertyInfo $propertyInfo)
    {
        $menuItem = (new VimMenuItemFactory())->createFromProperty($propertyInfo);

        $this->output[] = $menuItem->render();
    }
}
