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
use PHPCD\Element\ObjectElement\ReflectionMethod;
use PHPCD\NamespaceInfo;
use PHPCD\PHPCD;
use PHPCD\PHPFile\PHPFile;
use PHPCD\PHPFile\PHPFileFactory;
use PHPCD\View\View;
use Psr\Log\LoggerInterface as Logger;

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
        $constantRepository = Mockery::mock(ConstantRepository::class);
        $classConstantRepository = Mockery::mock(ClassConstantRepository::class);
        $propertyRepository = Mockery::mock(PropertyRepository::class);
        $methodRepository = Mockery::mock(MethodRepository::class);
        $fileFactory = Mockery::mock(PHPFileFactory::class);
        $view = Mockery::mock(View::class);
        $file = Mockery::mock(PHPFile::class);
        $legacyTypeLogic = new LegacyTypeLogic($logger, $fileFactory);
        $docBlock = Mockery::mock(DocBlock::class);
        $methodInfo = new ReflectionMethod(new \ReflectionMethod($class, $method), $docBlock);
        $functionRepository = Mockery::mock(FunctionRepository::class);

        $file->shouldReceive('getImports')->andReturn($imports);
        $file->shouldReceive('getNamespace')->andReturn($namespace);
        $fileFactory->shouldReceive('createFile')->andReturn($file);
        $view->shouldReceive('renderPHPFile')->andReturnNull();
        $methodRepository->shouldReceive('getByPath')->andReturn($methodInfo);

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

        $types = $phpcd->getTypesReturnedByMethod($class, $method);

        $this->assertEquals(count($expectedTypes), count($types));

        foreach ($expectedTypes as $expectedType) {
            $this->assertContains($expectedType, $types);
        }
    }

    public function dataProvider()
    {
        return [
            [
                'tests\\MethodRepository\\Sup',
                'baz',
                'tests\\MethodRepository',
                [],
                ['\\ReflectionClass', '\\tests\\MethodRepository\\Test1']
            ],
        ];
    }
}
