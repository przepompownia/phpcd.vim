<?php

namespace tests\PHPCD;

use PHPCD\PHPCD;
use PHPUnit\Framework\TestCase;
use Mockery;
use PHPCD\PHPFileInfo\PHPFileInfoFactory;
use PHPCD\PHPFileInfo\PHPFileInfo;
use PHPCD\ClassInfo\ClassInfoFactory;
use PHPCD\ConstantInfo\ConstantInfoRepository;
use PHPCD\ObjectElementInfo\MethodInfoRepository;
use PHPCD\ObjectElementInfo\PropertyInfoRepository;
use PHPCD\View\View;
use Psr\Log\LoggerInterface as Logger;
use PHPCD\NamespaceInfo;
use PHPCD\DocBlock\LegacyTypeLogic;

class FunctypeTest extends TestCase
{
    /**
     * @test
     * @dataProvider functypeDataProvider
     */
    public function functype($class, $method, $namespace, $imports, $expectedTypes)
    {
        $nsinfo = Mockery::mock(NamespaceInfo::class);
        $logger = Mockery::mock(Logger::class);
        $constantRepository = Mockery::mock(ConstantInfoRepository::class);
        $propertyInfoRepository = Mockery::mock(PropertyInfoRepository::class);
        $methodInfoRepository = Mockery::mock(MethodInfoRepository::class);
        $fileInfoFactory = Mockery::mock(PHPFileInfoFactory::class);
        $view = Mockery::mock(View::class);
        $fileInfo = Mockery::mock(PHPFileInfo::class);
        $legacyTypeLogic = new LegacyTypeLogic($logger, $fileInfoFactory);

        $fileInfo->shouldReceive('getImports')->andReturn($imports);
        $fileInfo->shouldReceive('getNamespace')->andReturn($namespace);
        $fileInfoFactory->shouldReceive('createFileInfo')->andReturn($fileInfo);
        $view->shouldReceive('renderPHPFileInfo')->andReturnNull();

        $phpcd = new PHPCD(
            $nsinfo,
            $logger,
            $constantRepository,
            $propertyInfoRepository,
            $methodInfoRepository,
            $fileInfoFactory,
            $view,
            $legacyTypeLogic
        );

        $types = $phpcd->functype($class, $method, true);

        $this->assertEquals(count($expectedTypes), count($types));

        foreach ($expectedTypes as $expectedType) {
            $this->assertContains($expectedType, $types);
        }
    }

    public function functypeDataProvider()
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
