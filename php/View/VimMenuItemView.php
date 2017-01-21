<?php

namespace PHPCD\View;

use PHPCD\ClassInfo\ClassInfo;
use PHPCD\ObjectElementInfo\MethodInfo;
use PHPCD\ObjectElementInfo\PropertyInfo;
use PHPCD\ConstantInfo\ConstantInfo;
use PHPCD\FunctionInfo\FunctionInfo;
use PHPCD\ObjectElementInfo\ObjectElementInfo;
use PHPCD\PHPFileInfo\PHPFileInfo;

class VimMenuItemView implements View
{
    /**
     *  @param array Map between modifier numbers and displayed symbols
     */
    private $modifier_symbols = [
       'final'     => '!',
       'private'    => '-',
       'protected'  => '#',
       'public'     => '+',
       'static'     => '@'
    ];

    private function getModifiers(ObjectElementInfo $objectElement)
    {
        return implode('', array_intersect_key($this->modifier_symbols, array_flip($objectElement->getModifiers())));
    }

    private function clearDoc($doc)
    {
        $doc = preg_replace('/[ \t]*\* ?/m', '', $doc);
        return preg_replace('#\s*\/|/\s*#', '', $doc);
    }

    public function renderConstantInfo(ConstantInfo $constantInfo)
    {
        $out = new VimMenuItem();
        $out->setWord($constantInfo->getName());
        $out->setAbbr(sprintf(" +@ %s %s", $constantInfo->getName(), $constantInfo->getValue()));
        $out->setKind('d');

        return $out->render();
    }

    public function renderClassInfo(ClassInfo $classInfo)
    {
        $out = new VimMenuItem();
        $out->setWord($classInfo->getName());
        $out->setAbbr();
        $out->setKind();
        $out->setInfo();
        return $out->render();
    }

    public function renderMethodInfo(MethodInfo $methodInfo)
    {
        $params = array_map(function ($param) {
            return $param->getName();
        }, $methodInfo->getParameters());

        $out = new VimMenuItem();
        $out->setWord($methodInfo->getName());
        $out->setAbbr(sprintf(
            "%3s %s (%s)",
            $this->getModifiers($methodInfo),
            $methodInfo->getName(),
            implode(', ', $params)
        ));
        $out->setKind('f');
        $out->setInfo($this->clearDoc($methodInfo->getDocComment()));

        return $out->render();
    }

    public function renderFunctionInfo(FunctionInfo $functionInfo)
    {
        $params = array_map(function ($param) {
            return $param->getName();
        }, $functionInfo->getParameters());

        $out = new VimMenuItem();
        $out->setWord($functionInfo->getName());
        $out->setAbbr(sprintf('%s(%s)', $functionInfo->getName(), $params));
        $out->setKind('f');
        $out->setInfo(preg_replace('#/?\*(\*|/)?#', '', $functionInfo->getDocComment()));

        return $out->render();
    }

    public function renderPropertyInfo(PropertyInfo $propertyInfo)
    {
        $modifiers = $this->getModifiers($propertyInfo);
        $out = new VimMenuItem();
        $out->setWord($propertyInfo->getName());
        $out->setAbbr(sprintf("%3s %s", $modifiers, $propertyInfo->getName()));
        $out->setKind('p');
        $out->setInfo(preg_replace('#/?\*(\*|/)?#', '', $propertyInfo->getDocComment()));

        return $out->render();
    }

    public function renderPHPFileInfo(PHPFileInfo $fileInfo)
    {
        return [
            'namespace' => $fileInfo->getNamespace(),
            'class' => $fileInfo->getClassName(),
            'imports' => $fileInfo->getImports()
        ];
    }
}
