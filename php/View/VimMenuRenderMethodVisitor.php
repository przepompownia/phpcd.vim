<?php

namespace PHPCD\View;

use PHPCD\Element\ObjectElement\MethodInfo;

class VimMenuRenderMethodVisitor extends VimMenuRenderAbstractVisitor implements MethodVisitor
{
    public function visitElement(MethodInfo $methodInfo)
    {
        $menuItem = (new VimMenuItemFactory())->createFromMethod($methodInfo);

        $this->output[] = $menuItem->render();
    }
}
