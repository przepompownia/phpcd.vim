<?php

namespace PHPCD\View;

use PHPCD\Collection\Collection;
use PHPCD\Element\ClassInfo\ClassCollection;
use PHPCD\Element\ObjectElement\Constant\ClassConstantCollection;
use PHPCD\Element\ConstantInfo\ConstantCollection;
use PHPCD\Element\FunctionInfo\FunctionCollection;
use PHPCD\Element\ObjectElement\MethodCollection;
use PHPCD\Element\ObjectElement\ObjectElementCollection;
use PHPCD\Element\ObjectElement\PropertyCollection;
use PHPCD\PHPFile\PHPFile;

class VimMenuItemView implements View
{
    public function renderConstantCollection(ConstantCollection $collection)
    {
        $visitor = new VimMenuRenderConstantVisitor();

        return $this->renderCollectionWithVisitor($collection, $visitor);
    }

    public function renderClassConstantCollection(ClassConstantCollection $collection)
    {
        $visitor = new VimMenuRenderClassConstantVisitor();

        return $this->renderCollectionWithVisitor($collection, $visitor);
    }

    public function renderClassCollection(ClassCollection $collection)
    {
        $visitor = new VimMenuRenderClassVisitor();

        return $this->renderCollectionWithVisitor($collection, $visitor);
    }

    public function renderMethodCollection(MethodCollection $collection)
    {
        $visitor = new VimMenuRenderMethodVisitor();

        return $this->renderCollectionWithVisitor($collection, $visitor);
    }

    public function renderFunctionCollection(FunctionCollection $collection)
    {
        $visitor = new VimMenuRenderFunctionVisitor();

        return $this->renderCollectionWithVisitor($collection, $visitor);
    }

    public function renderPropertyCollection(PropertyCollection $collection)
    {
        $visitor = new VimMenuRenderPropertyVisitor();

        return $this->renderCollectionWithVisitor($collection, $visitor);
    }

    public function renderObjectElementCollection(ObjectElementCollection $collection)
    {
        $visitor = new VimMenuRenderObjectElementVisitor(new VimMenuItemFactory());

        $collection->acceptObjectElement($visitor);

        return $visitor->getOutput();
    }

    public function renderPHPFile(PHPFile $file)
    {
        return [
            'namespace' => $file->getNamespace(),
            'class' => $file->getClassName(),
            'imports' => $file->getImports(),
        ];
    }

    private function renderCollectionWithVisitor(Collection $collection, VimMenuRenderAbstractVisitor $visitor)
    {
        $collection->accept($visitor);

        return $visitor->getOutput();
    }
}
