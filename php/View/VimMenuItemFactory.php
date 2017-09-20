<?php

declare(strict_types=1);

namespace PHPCD\View;

use PHPCD\Element\ClassInfo\ClassInfo;
use PHPCD\Element\ObjectElement\Constant\ClassConstant;
use PHPCD\Element\ObjectElement\MethodInfo;
use PHPCD\Element\ObjectElement\ObjectElement;
use PHPCD\Element\ObjectElement\PropertyInfo;

class VimMenuItemFactory
{
    /**
     * @var array Map between modifier numbers and displayed symbols
     */
    private $modifierSymbols = [
        'final'     => '!',
        'private'   => '-',
        'protected' => '#',
        'public'    => '+',
        'static'    => '@',
    ];

    private function clearDoc($doc)
    {
        $doc = preg_replace('/[ \t]*\* ?/m', '', $doc);

        return preg_replace('#\s*\/|/\s*#', '', $doc);
    }

    protected function getModifiers(ObjectElement $objectElement): string
    {
        return implode('', array_intersect_key($this->modifierSymbols, array_flip($objectElement->getModifiers())));
    }

    public function createFromClassConstant(ClassConstant $classConstant): VimMenuItem
    {
        $menuItem = new VimMenuItem();
        $menuItem->setWord($classConstant->getName());

        $value = $classConstant->getValue();
        if (is_array($value)) {
            $value = sprintf('[ %s, ... ]', implode(', ', array_slice($value, 0, 2, false)));
        }
        $menuItem->setAbbr(sprintf(' +@ %s %s', $classConstant->getName(), $value));
        $menuItem->setKind('d');

        return $menuItem;
    }

    public function createFromProperty(PropertyInfo $propertyInfo): VimMenuItem
    {
        $modifiers = $this->getModifiers($propertyInfo);
        $menuItem = new VimMenuItem();
        $menuItem->setWord($propertyInfo->getName());
        $menuItem->setAbbr(sprintf('%3s %s', $modifiers, $propertyInfo->getName()));
        $menuItem->setKind('p');
        $menuItem->setInfo(preg_replace('#/?\*(\*|/)?#', '', $propertyInfo->getDocComment()));

        return $menuItem;
    }

    public function createFromMethod(MethodInfo $methodInfo): VimMenuItem
    {
        $params = array_map(function ($param) {
            return $param->getName();
        }, $methodInfo->getParameters());

        $menuItem = new VimMenuItem();
        $menuItem->setWord($methodInfo->getName());
        $menuItem->setAbbr(sprintf(
            '%3s %s (%s)',
            $this->getModifiers($methodInfo),
            $methodInfo->getName(),
            implode(', ', $params)
        ));
        $menuItem->setKind('f');
        $menuItem->setInfo($this->clearDoc($methodInfo->getDocComment()));

        return $menuItem;
    }

    public function createFromClass(ClassInfo $classInfo): VimMenuItem
    {
        $menuItem = new VimMenuItem();
        $menuItem->setWord($classInfo->getName());
        $menuItem->setAbbr('');
        $menuItem->setKind('');
        $menuItem->setInfo('');

        return $menuItem;
    }
}
