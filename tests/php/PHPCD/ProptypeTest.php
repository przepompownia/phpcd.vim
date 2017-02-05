<?php

namespace tests\PHPCD;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\ObjectElementInfo\ReflectionPropertyInfo;
use PHPCD\PHPCD;
use Mockery;
use PHPCD\PHPFileInfo\PHPFileInfoFactory;
use PHPCD\PHPFileInfo\PHPFileInfo;
use PHPCD\ClassInfo\ClassInfoFactory;
use PHPCD\ConstantInfo\ClassConstantInfoRepository;
use PHPCD\ConstantInfo\ConstantInfoRepository;
use PHPCD\ObjectElementInfo\MethodInfoRepository;
use PHPCD\ObjectElementInfo\PropertyInfoRepository;
use PHPCD\NamespaceInfo;
use PHPCD\View\View;
use Psr\Log\LoggerInterface as Logger;
use PHPCD\DocBlock\LegacyTypeLogic;
use PHPCD\DocBlock\DocBlock;
use PHPCD\Element\FunctionInfo\FunctionRepository;

class ProptypeTest extends MockeryTestCase
{
    /**
     * @test
     * @dataProvider proptypeDataProvider
     */
    public function proptype($class, $property, $namespace, $imports, $expectedTypes)
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
        $propertyInfo = new ReflectionPropertyInfo(new \ReflectionProperty($class, $property), $docBlock);
        $functionRepository = Mockery::mock(FunctionRepository::class);

        $fileInfo->shouldReceive('getImports')->andReturn($imports);
        $fileInfo->shouldReceive('getNamespace')->andReturn($namespace);
        $fileInfoFactory->shouldReceive('createFileInfo')->andReturn($fileInfo);
        $view->shouldReceive('renderPHPFileInfo')->andReturnNull();
        $propertyInfoRepository->shouldReceive('getByPath')->andReturn($propertyInfo);

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

        $types = $phpcd->getTypesOfProperty($class, $property);

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
            [
                'tests\\MethodInfoRepository\\Sup',
                'pub6',
                'tests\\MethodInfoRepository',
                ['PM' => '\\PHPCD\\PatternMatcher'],
                ['\\PHPCD\\PatternMatcher\\PatternMatcher']
            ],
        ];
    }
}
