<?php

namespace tests\PHPCD;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery;
use PHPCD\Element\ConstantInfo\ClassConstantRepository;
use PHPCD\Element\ConstantInfo\ConstantRepository;
use PHPCD\Element\FunctionInfo\FunctionRepository;
use PHPCD\Element\ObjectElement\MethodRepository;
use PHPCD\Element\ObjectElement\PropertyRepository;
use PHPCD\NamespaceInfo;
use PHPCD\PHPCD;
use PHPCD\PHPFile\PHPFileFactory;
use PHPCD\View\VimMenuItemView;
use Psr\Log\LoggerInterface as Logger;
use PHPCD\DocBlock\DocBlock;

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
            $functionRepository
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
