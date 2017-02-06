<?php

namespace PHPCD\View;

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
        $collection->accept($visitor);
        return $visitor->getOutput();
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
        $collection->accept($visitor);
        return $visitor->getOutput();
    }

    public function renderFunctionCollection(FunctionCollection $collection)
    {
        $visitor = new VimMenuRenderFunctionVisitor();
        $collection->accept($visitor);
        return $visitor->getOutput();
    }

    public function renderPropertyCollection(PropertyInfoCollection $collection)
    {
        $visitor = new VimMenuRenderPropertyVisitor();
        $collection->accept($visitor);
        return $visitor->getOutput();
    }

    public function renderPHPFileInfo(PHPFileInfo $fileInfo)
    {
        return [
            'namespace' => $fileInfo->getNamespace(),
            'class' => $fileInfo->getClassName(),
            'imports' => $fileInfo->getImports(),
        ];
    }
}
