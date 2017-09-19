<?php

namespace tests\PHPCD;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery;
use PHPCD\Element\ObjectElement\CompoundObjectElementRepository;
use PHPCD\Element\ObjectElement\Constant\ClassConstant;
use PHPCD\Element\ObjectElement\Constant\ClassConstantRepository;
use PHPCD\Element\ConstantInfo\ConstantRepository;
use PHPCD\Element\FunctionInfo\FunctionRepository;
use PHPCD\Element\ObjectElement\MethodRepository;
use PHPCD\Element\ObjectElement\PropertyRepository;
use PHPCD\Element\PhysicalLocation;
use PHPCD\NamespaceInfo;
use PHPCD\PHPCD;
use PHPCD\PHPFile\PHPFileFactory;
use PHPCD\View\VimMenuItemView;
use Psr\Log\LoggerInterface as Logger;
use PHPCD\DocBlock\DocBlock;
use PHPCD\NotFoundException;

class FindSymbolDeclarationTest extends MockeryTestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function findSymbolDeclaration($class, $symbol, $expectedFilePath, $expectedLineNumber)
    {
        $nsinfo = Mockery::mock(NamespaceInfo::class);
        $logger = Mockery::mock(Logger::class);
        $propertyRepository = Mockery::mock(PropertyRepository::class);
        $methodRepository = Mockery::mock(MethodRepository::class);
        $fileFactory = Mockery::mock(PHPFileFactory::class);

        $constantRepository = Mockery::mock(ConstantRepository::class);
        $classConstantRepository = Mockery::mock(ClassConstantRepository::class);
        $functionRepository = Mockery::mock(FunctionRepository::class);

        $methodRepository->shouldReceive('getByPath')->andThrow(NotFoundException::class);
        $location = Mockery::mock(PhysicalLocation::class);
        $location->shouldReceive('getFileName')->andReturn(realpath($expectedFilePath));
        $location->shouldReceive('getLineNumber')->andReturn($expectedLineNumber);
        $constant = Mockery::mock(ClassConstant::class);
        $constant->shouldReceive('getPhysicalLocation')->andReturn($location);

        $objectElementRepository = Mockery::mock(CompoundObjectElementRepository::class);
        $objectElementRepository->shouldReceive('findObjectElement')->andReturn($constant);

        $docBlock = Mockery::mock(DocBlock::class);

        $view = new VimMenuItemView();

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
            $objectElementRepository
        );

        $output = $phpcd->findSymbolDeclaration($class, $symbol);
        $this->assertEquals([realpath($expectedFilePath), $expectedLineNumber], $output);
    }

    public function dataProvider()
    {
        return [
            [
                '\tests\Fixtures\MethodRepository\Sup',
                'baz',
                __DIR__.'/../Fixtures/MethodRepository/Sup.php',
                26,
            ],
            [
                '\tests\Fixtures\MethodRepository\Sup',
                'pub5',
                __DIR__.'/../Fixtures/MethodRepository/Sup.php',
                16,
            ],
        ];
    }
}
