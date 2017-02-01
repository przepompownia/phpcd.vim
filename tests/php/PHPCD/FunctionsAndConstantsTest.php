<?php

namespace tests\PHPCD;

use PHPCD\View\VimMenuItemView;
use PHPCD\FunctionInfo\ReflectionFunctionInfo;
use PHPCD\FunctionInfo\FunctionCollection;
use PHPCD\ConstantInfo\ConstantInfoCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\ObjectElementInfo\PropertyInfo;
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
use PHPCD\FunctionInfo\FunctionRepository;

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
        $propertyInfoRepository = Mockery::mock(PropertyInfoRepository::class);
        $methodInfoRepository = Mockery::mock(MethodInfoRepository::class);
        $fileInfoFactory = Mockery::mock(PHPFileInfoFactory::class);
        $legacyTypeLogic = Mockery::mock(LegacyTypeLogic::class);

        $constantRepository = Mockery::mock(ConstantInfoRepository::class);
        $classConstantRepository = Mockery::mock(ClassConstantInfoRepository::class);
        $functionRepository = Mockery::mock(FunctionRepository::class);

        $view = new VimMenuItemView();
        $constantRepository->shouldReceive('find')->once()->andReturn(new ConstantInfoCollection());
        $functionCollection = new FunctionCollection();
        $functionCollection->add(new ReflectionFunctionInfo(new \ReflectionFunction('var_dump')));
        $functionRepository->shouldReceive('find')->once()->andReturn($functionCollection);


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
