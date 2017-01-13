<?php

namespace PHPCD;

use PHPUnit\Framework\TestCase;
use Mockery;
use PHPCD\PHPFileInfo\PHPFileInfoFactory;
use PHPCD\PHPFileInfo\PHPFileInfo;
use PHPCD\ClassInfo\ClassInfoFactory;
use PHPCD\ConstantInfo\ConstantInfoRepository;
use PHPCD\ObjectElementInfo\MethodInfoRepository;
use PHPCD\ObjectElementInfo\PropertyInfoRepository;
use PHPCD\View\View;
use Psr\Log\LoggerInterface as Logger;

class PHPCDTest extends TestCase
{
    /**
     * @test
     */
    public function proptype()
    {
        $nsinfo = Mockery::mock(NamespaceInfo::class);
        $logger = Mockery::mock(Logger::class);
        $constantRepository = Mockery::mock(ConstantInfoRepository::class);
        $propertyInfoRepository = Mockery::mock(PropertyInfoRepository::class);
        $methodInfoRepository = Mockery::mock(MethodInfoRepository::class);
        $fileInfoFactory = Mockery::mock(PHPFileInfoFactory::class);
        $view = Mockery::mock(View::class);
        $fileInfo = Mockery::mock(PHPFileInfo::class);

        $fileInfo->shouldReceive('getImports')->andReturn([]);
        $fileInfo->shouldReceive('getNamespace')->andReturn('');
        $fileInfoFactory->shouldReceive('createFileInfo')->andReturn($fileInfo);
        $view->shouldReceive('renderPHPFileInfo')->andReturnNull();

        $phpcd = new PHPCD(
            $nsinfo,
            $logger,
            $constantRepository,
            $propertyInfoRepository,
            $methodInfoRepository,
            $fileInfoFactory,
            $view
        );

        // $phpcd->proptype('PHPCD\\PHPCD', 'logger');
        var_dump($phpcd->functype('PHPCD\\ClassInfo\\ClassInfoRepository', 'find', true));
    }
}
