<?php

namespace tests\PHPCD;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\Element\ConstantInfo\ConstantRepository;
use PHPCD\Element\FunctionInfo\FunctionRepository;
use PHPCD\Element\ObjectElement\CompoundObjectElementRepository;
use PHPCD\Element\ObjectElement\Constant\ClassConstant;
use PHPCD\Element\PhysicalLocation;
use PHPCD\NamespaceInfo;
use PHPCD\PHPCD;
use PHPCD\PHPFile\PHPFileFactory;
use PHPCD\View\VimMenuItemView;
use Psr\Log\LoggerInterface as Logger;

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
        $fileFactory = Mockery::mock(PHPFileFactory::class);

        $constantRepository = Mockery::mock(ConstantRepository::class);
        $functionRepository = Mockery::mock(FunctionRepository::class);

        $location = Mockery::mock(PhysicalLocation::class);
        $location->shouldReceive('getFileName')->andReturn(realpath($expectedFilePath));
        $location->shouldReceive('getLineNumber')->andReturn($expectedLineNumber);
        $constant = Mockery::mock(ClassConstant::class);
        $constant->shouldReceive('getPhysicalLocation')->andReturn($location);

        $objectElementRepository = Mockery::mock(CompoundObjectElementRepository::class);
        $objectElementRepository->shouldReceive('findObjectElement')->andReturn($constant);

        $view = new VimMenuItemView();

        $phpcd = new PHPCD(
            $nsinfo,
            $logger,
            $constantRepository,
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
