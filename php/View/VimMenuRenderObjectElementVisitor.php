<?php

declare(strict_types=1);

namespace PHPCD\View;

use PHPCD\Element\ObjectElement\Constant\ClassConstant;
use PHPCD\Element\ObjectElement\MethodInfo;
use PHPCD\Element\ObjectElement\ObjectElementCollection;
use PHPCD\Element\ObjectElement\PropertyInfo;

class VimMenuRenderObjectElementVisitor extends VimMenuRenderAbstractVisitor implements ObjectElementVisitor
{
    /**
     * @var VimMenuItemFactory
     */
    private $itemFactory;

    public function __construct(VimMenuItemFactory $itemFactory)
    {
        $this->itemFactory = $itemFactory;
    }

    public function visitCollection(ObjectElementCollection $collection)
    {
        foreach ($collection as $objectElement) {
            $objectElement->acceptObjectElement($this);
        }
    }

    public function visitProperty(PropertyInfo $propertyInfo)
    {
        $menuItem = $this->itemFactory->createFromProperty($propertyInfo);

        $this->output[] = $menuItem->render();
    }

    public function visitMethod(MethodInfo $methodInfo)
    {
        $menuItem = $this->itemFactory->createFromMethod($methodInfo);

        $this->output[] = $menuItem->render();
    }

    public function visitConstant(ClassConstant $classConstant)
    {
        $menuItem = $this->itemFactory->createFromClassConstant($classConstant);

        $this->output[] = $menuItem->render();
    }
}
