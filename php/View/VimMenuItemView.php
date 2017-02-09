<?php

namespace PHPCD\View;

use PHPCD\Collection\Collection;
use PHPCD\Element\ClassInfo\ClassInfoCollection;
use PHPCD\Element\ConstantInfo\ClassConstantInfoCollection;
use PHPCD\Element\ConstantInfo\ConstantInfoCollection;
use PHPCD\Element\FunctionInfo\FunctionCollection;
use PHPCD\Element\ObjectElementInfo\MethodInfoCollection;
use PHPCD\Element\ObjectElementInfo\PropertyInfoCollection;
use PHPCD\PHPFileInfo\PHPFileInfo;

class VimMenuItemView implements View
{
    public function renderConstantInfoCollection(ConstantInfoCollection $collection)
    {
        $visitor = new VimMenuRenderConstantVisitor();

        return $this->renderCollectionWithVisitor($collection, $visitor);
    }

    public function renderClassConstantCollection(ClassConstantInfoCollection $collection)
    {
        $visitor = new VimMenuRenderClassConstantVisitor();

        return $this->renderCollectionWithVisitor($collection, $visitor);
    }

    public function renderClassInfoCollection(ClassInfoCollection $collection)
    {
        $visitor = new VimMenuRenderClassVisitor();

        return $this->renderCollectionWithVisitor($collection, $visitor);
    }

    public function renderMethodCollection(MethodInfoCollection $collection)
    {
        $visitor = new VimMenuRenderMethodVisitor();

        return $this->renderCollectionWithVisitor($collection, $visitor);
    }

    public function renderFunctionCollection(FunctionCollection $collection)
    {
        $visitor = new VimMenuRenderFunctionVisitor();

        return $this->renderCollectionWithVisitor($collection, $visitor);
    }

    public function renderPropertyCollection(PropertyInfoCollection $collection)
    {
        $visitor = new VimMenuRenderPropertyVisitor();

        return $this->renderCollectionWithVisitor($collection, $visitor);
    }

    public function renderPHPFileInfo(PHPFileInfo $fileInfo)
    {
        return [
            'namespace' => $fileInfo->getNamespace(),
            'class' => $fileInfo->getClassName(),
            'imports' => $fileInfo->getImports(),
        ];
    }

    private function renderCollectionWithVisitor(Collection $collection, VimMenuRenderAbstractVisitor $visitor)
    {
        $collection->accept($visitor);

        return $visitor->getOutput();
    }
}
