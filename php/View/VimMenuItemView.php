<?php

namespace PHPCD\View;

use PHPCD\View\VimMenuRenderAbstractVisitor;
use PHPCD\Collection\Collection;
use PHPCD\Element\ObjectElementInfo\PropertyInfoCollection;
use PHPCD\Element\ObjectElementInfo\MethodInfoCollection;
use PHPCD\Element\FunctionInfo\FunctionCollection;
use PHPCD\Element\ConstantInfo\ConstantInfoCollection;
use PHPCD\Element\ClassInfo\ClassInfo;
use PHPCD\Element\ObjectElementInfo\PropertyInfo;
use PHPCD\Element\ConstantInfo\ConstantInfo;
use PHPCD\Element\FunctionInfo\FunctionInfo;
use PHPCD\Element\ObjectElementInfo\ObjectElementInfo;
use PHPCD\PHPFileInfo\PHPFileInfo;

class VimMenuItemView implements View
{
    public function renderConstantInfoCollection(ConstantInfoCollection $collection)
    {
        $visitor = new VimMenuRenderConstantVisitor();

        return $this->renderCollectionWithVisitor($collection, $visitor);
    }

    public function renderClassInfo(ClassInfo $classInfo)
    {
        $out = new VimMenuItem();
        $out->setWord($classInfo->getName());
        $out->setAbbr('');
        $out->setKind('');
        $out->setInfo('');

        return $out->render();
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
