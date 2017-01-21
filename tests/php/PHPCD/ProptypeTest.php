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
use PHPCD\NamespaceInfo;
use PHPCD\View\View;
use Psr\Log\LoggerInterface as Logger;
use PHPCD\DocBlock\LegacyTypeLogic;

class ProptypeTest extends TestCase
{
    /**
     * @test
     * @dataProvider proptypeDataProvider
     */
    public function proptype($class, $method, $namespace, $imports, $expectedTypes)
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

        $types = $phpcd->proptype($class, $method, true);

        $this->assertEquals(count($expectedTypes), count($types));

        foreach ($expectedTypes as $expectedType) {
            $this->assertContains($expectedType, $types);
        }
    }

    public function proptypeDataProvider()
    {
        return [
            [
                'tests\\MethodInfoRepository\\Sup',
                'pub5',
                'tests\\MethodInfoRepository',
                [],
                ['\\ReflectionClass', '\\tests\\MethodInfoRepository\\Test1']
            ],
        ];
    }
}
