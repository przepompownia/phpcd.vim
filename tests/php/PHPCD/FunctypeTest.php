<?php

namespace tests\PHPCD;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\Element\ObjectElementInfo\ReflectionMethodInfo;
use PHPCD\PHPCD;
use Mockery;
use PHPCD\PHPFileInfo\PHPFileInfoFactory;
use PHPCD\PHPFileInfo\PHPFileInfo;
use PHPCD\Element\ClassInfo\ClassInfoFactory;
use PHPCD\Element\ConstantInfo\ClassConstantInfoRepository;
use PHPCD\Element\ConstantInfo\ConstantInfoRepository;
use PHPCD\Element\ObjectElementInfo\MethodInfoRepository;
use PHPCD\Element\ObjectElementInfo\PropertyInfoRepository;
use PHPCD\View\View;
use Psr\Log\LoggerInterface as Logger;
use PHPCD\NamespaceInfo;
use PHPCD\DocBlock\LegacyTypeLogic;
use PHPCD\Element\FunctionInfo\FunctionRepository;

class FunctypeTest extends MockeryTestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function getTypesReturnedByMethod($class, $method, $namespace, $imports, $expectedTypes)
    {
        $nsinfo = Mockery::mock(NamespaceInfo::class);
        $logger = Mockery::mock(Logger::class);
        $constantRepository = Mockery::mock(ConstantInfoRepository::class);
        $classConstantRepository = Mockery::mock(ClassConstantInfoRepository::class);
        $propertyInfoRepository = Mockery::mock(PropertyInfoRepository::class);
        $methodInfoRepository = Mockery::mock(MethodInfoRepository::class);
        $fileInfoFactory = Mockery::mock(PHPFileInfoFactory::class);
        $view = Mockery::mock(View::class);
        $fileInfo = Mockery::mock(PHPFileInfo::class);
        $legacyTypeLogic = new LegacyTypeLogic($logger, $fileInfoFactory);
        $docBlock = Mockery::mock(DocBlock::class);
        $methodInfo = new ReflectionMethodInfo(new \ReflectionMethod($class, $method), $docBlock);
        $functionRepository = Mockery::mock(FunctionRepository::class);

        $fileInfo->shouldReceive('getImports')->andReturn($imports);
        $fileInfo->shouldReceive('getNamespace')->andReturn($namespace);
        $fileInfoFactory->shouldReceive('createFileInfo')->andReturn($fileInfo);
        $view->shouldReceive('renderPHPFileInfo')->andReturnNull();
        $methodInfoRepository->shouldReceive('getByPath')->andReturn($methodInfo);

        $phpcd = new PHPCD(
            $nsinfo,
            $logger,
            $constantRepository,
            $classConstantRepository,
            $propertyInfoRepository,
            $methodInfoRepository,
            $fileInfoFactory,
            $view,
            $functionRepository,
            $legacyTypeLogic
        );

        $types = $phpcd->getTypesReturnedByMethod($class, $method, true);

        $this->assertEquals(count($expectedTypes), count($types));

        foreach ($expectedTypes as $expectedType) {
            $this->assertContains($expectedType, $types);
        }
    }

    public function dataProvider()
    {
        return [
            [
                'tests\\MethodInfoRepository\\Sup',
                'baz',
                'tests\\MethodInfoRepository',
                [],
                ['\\ReflectionClass', '\\tests\\MethodInfoRepository\\Test1']
            ],
        ];
    }
}
