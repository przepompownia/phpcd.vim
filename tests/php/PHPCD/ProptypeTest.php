<?php

namespace tests\PHPCD;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\DocBlock\DocBlock;
use PHPCD\DocBlock\LegacyTypeLogic;
use PHPCD\Element\ConstantInfo\ClassConstantRepository;
use PHPCD\Element\ConstantInfo\ConstantRepository;
use PHPCD\Element\FunctionInfo\FunctionRepository;
use PHPCD\Element\ObjectElement\MethodRepository;
use PHPCD\Element\ObjectElement\PropertyRepository;
use PHPCD\Element\ObjectElement\ReflectionProperty;
use PHPCD\NamespaceInfo;
use PHPCD\PHPCD;
use PHPCD\PHPFile\PHPFile;
use PHPCD\PHPFile\PHPFileFactory;
use PHPCD\View\View;
use Psr\Log\LoggerInterface as Logger;

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
        $constantRepository = Mockery::mock(ConstantRepository::class);
        $classConstantRepository = Mockery::mock(ClassConstantRepository::class);
        $propertyRepository = Mockery::mock(PropertyRepository::class);
        $methodRepository = Mockery::mock(MethodRepository::class);
        $fileFactory = Mockery::mock(PHPFileFactory::class);
        $view = Mockery::mock(View::class);
        $file = Mockery::mock(PHPFile::class);
        $legacyTypeLogic = new LegacyTypeLogic($logger, $fileFactory);
        $docBlock = Mockery::mock(DocBlock::class);
        $propertyInfo = new ReflectionProperty(new \ReflectionProperty($class, $property), $docBlock);
        $functionRepository = Mockery::mock(FunctionRepository::class);

        $file->shouldReceive('getImports')->andReturn($imports);
        $file->shouldReceive('getNamespace')->andReturn($namespace);
        $fileFactory->shouldReceive('createFile')->andReturn($file);
        $view->shouldReceive('renderPHPFile')->andReturnNull();
        $propertyRepository->shouldReceive('getByPath')->andReturn($propertyInfo);

        $phpcd = new PHPCD(
            $nsinfo,
            $logger,
            $constantRepository,
            $classConstantRepository,
            $propertyRepository,
            $methodRepository,
            $fileFactory,
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
                'tests\\MethodRepository\\Sup',
                'pub5',
                'tests\\MethodRepository',
                [],
                ['\\ReflectionClass', '\\tests\\MethodRepository\\Test1']
            ],
            [
                'tests\\MethodRepository\\Sup',
                'pub6',
                'tests\\MethodRepository',
                ['PM' => '\\PHPCD\\PatternMatcher'],
                ['\\PHPCD\\PatternMatcher\\PatternMatcher']
            ],
        ];
    }
}
