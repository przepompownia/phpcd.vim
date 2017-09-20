<?php

namespace PHPCD\View;

use PHPCD\Element\ClassInfo\ClassInfo;

class VimMenuRenderClassVisitor extends VimMenuRenderAbstractVisitor implements ClassVisitor
{
    public function visitElement(ClassInfo $classInfo)
    {
        $factory = new VimMenuItemFactory();
        $menuItem = $factory->createFromClass($classInfo);

        $this->output[] = $menuItem->render();
    }
}
