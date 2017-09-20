<?php

namespace PHPCD\View;

use PHPCD\Element\ObjectElement\Constant\ClassConstant;

class VimMenuRenderClassConstantVisitor extends VimMenuRenderAbstractVisitor implements ClassConstantVisitor
{
    public function visitElement(ClassConstant $constantInfo)
    {
        $menuItem = (new VimMenuItemFactory())->createFromClassConstant($constantInfo);

        $this->output[] = $menuItem->render();
    }
}
