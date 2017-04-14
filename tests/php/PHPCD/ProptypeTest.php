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
use PHPCD\Element\ObjectElement\ReflectionProperty;
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
use tests\Fixtures\MethodRepository\Test1;

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
        $docBlockFactory = DocblockFactory::createInstance();
        $contextFactory = new ContextFactory();
        $docBlock = new DocBlock($docBlockFactory, $contextFactory);
        $propertyInfo = new ReflectionProperty($docBlock, new \ReflectionProperty($class, $property));
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
            $functionRepository
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
                Sup::class,
                'pub5',
                'tests\\Fixtures\\MethodRepository',
                [],
                ['\\ReflectionClass', '\\'.Test1::class]
            ],
            [
                Sup::class,
                'pub6',
                'tests\\Fixtures\\MethodRepository',
                ['PM' => '\\'.PatternMatcher::class],
                ['\\'.PatternMatcher::class]
            ],
        ];
    }
}
