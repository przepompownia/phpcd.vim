<?php

namespace tests\PHPCD;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\Element\ConstantInfo\ConstantCollection;
use PHPCD\Element\ConstantInfo\ConstantRepository;
use PHPCD\Element\FunctionInfo\FunctionCollection;
use PHPCD\Element\FunctionInfo\FunctionRepository;
use PHPCD\Element\FunctionInfo\ReflectionFunction;
use PHPCD\Element\ObjectElement\CompoundObjectElementRepository;
use PHPCD\Element\ObjectElement\Constant\ClassConstantRepository;
use PHPCD\Element\ObjectElement\MethodRepository;
use PHPCD\Element\ObjectElement\PropertyRepository;
use PHPCD\NamespaceInfo;
use PHPCD\PHPCD;
use PHPCD\PHPFile\PHPFileFactory;
use PHPCD\View\VimMenuItemView;
use Psr\Log\LoggerInterface as Logger;
use PHPCD\DocBlock\DocBlock;

class FunctionsAndConstantsTest extends MockeryTestCase
{
    /**
     * @test
     * @dataProvider proptypeDataProvider
     */
    public function getFunctionsAndConstants($pattern, $result)
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
        $constantRepository->shouldReceive('find')->once()->andReturn(new ConstantCollection());
        $functionCollection = new FunctionCollection();
        $functionCollection->add(new ReflectionFunction($docBlock, new \ReflectionFunction('var_dump')));
        $functionRepository->shouldReceive('find')->once()->andReturn($functionCollection);
        $objectElementRepository = Mockery::mock(CompoundObjectElementRepository::class);

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

        $output = $phpcd->getFunctionsAndConstants('var');
        $this->assertCount(1, $output);
        $firstItem = current($output);
        $this->assertEquals('var_dump', $firstItem['word']);
        $this->assertEquals('var_dump(vars)', $firstItem['abbr']);
        $this->assertEquals('f', $firstItem['kind']);
    }

    public function proptypeDataProvider()
    {
        return [
            [1,1]
        ];
    }
}
