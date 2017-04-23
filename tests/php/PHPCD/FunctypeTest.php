<?php

namespace tests\PHPCD;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\DocBlock\DocBlock;
use PHPCD\Element\ConstantInfo\ClassConstantRepository;
use PHPCD\Element\ConstantInfo\ConstantRepository;
use PHPCD\Element\FunctionInfo\FunctionRepository;
use PHPCD\Element\ObjectElement\MethodRepository;
use PHPCD\Element\ObjectElement\PropertyRepository;
use PHPCD\Element\ObjectElement\ReflectionMethod;
use PHPCD\NamespaceInfo;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\PHPCD;
use PHPCD\PHPFile\PHPFile;
use PHPCD\PHPFile\PHPFileFactory;
use PHPCD\View\View;
use Psr\Log\LoggerInterface as Logger;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\ContextFactory;
use tests\Fixtures\MethodRepository\Sup;
use tests\Fixtures\MethodRepository\SupPhp7;
use tests\Fixtures\MethodRepository\Test1;

class FunctypeTest extends MockeryTestCase
{
    /**
     * @test
     * @group php7
     * @dataProvider dataProviderPHP7
     */
    public function getTypesReturnedByMethodForPHP7($class, $method, $namespace, $imports, $expectedTypes)
    {
        return $this->getTypes($class, $method, $namespace, $imports, $expectedTypes);
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function getTypesReturnedByMethod($class, $method, $namespace, $imports, $expectedTypes)
    {
        return $this->getTypes($class, $method, $namespace, $imports, $expectedTypes);
    }

    private function getTypes($class, $method, $namespace, $imports, $expectedTypes)
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
        $docBlockFactory = DocblockFactory::createInstance();
        $contextFactory = new ContextFactory();
        $docBlock = new DocBlock($docBlockFactory, $contextFactory);
        $methodInfo = new ReflectionMethod($docBlock, new \ReflectionMethod($class, $method));
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
            $functionRepository
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
                 Sup::class,
                 'baz',
                 'tests\\Fixtures\\MethodRepository',
                 [],
                 ['\\ReflectionClass', '\\'.Test1::class]
             ],
        ];
    }

    public function dataProviderPHP7()
    {
        return [
            [
                SupPhp7::class,
                'doNothing',
                'tests\\Fixtures\\MethodRepository',
                [],
                ['\\'.PatternMatcher::class]
            ],
        ];
    }
}
