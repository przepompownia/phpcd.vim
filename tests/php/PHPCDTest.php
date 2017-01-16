<?php

namespace PHPCD;

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

class PHPCDTest extends TestCase
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
            $view
        );

        foreach ($expectedTypes as $expectedType) {
            $types = $phpcd->functype($class, $method, true);
            $this->assertContains($expectedType, $types);
        }
    }

    public function functypeDataProvider()
    {
        return [
            [
                'PHPCD\\ClassInfo\\ClassInfoRepository',
                'find',
                'PHPCD\\ClassInfo',
                [],
                ['\PHPCD\ClassInfo\ClassInfoCollection']
            ],
        ];
    }

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
            $view
        );

        foreach ($expectedTypes as $expectedType) {
            $types = $phpcd->proptype($class, $method, true);
            $this->assertContains($expectedType, $types);
        }
    }

    public function proptypeDataProvider()
    {
        return [
            [
                'PHPCD\\MethodInfoRepository\\Sup',
                'pub5',
                'PHPCD\\MethodInfoRepository',
                [],
                ['\ReflectionClass']
            ],
        ];
    }
}
