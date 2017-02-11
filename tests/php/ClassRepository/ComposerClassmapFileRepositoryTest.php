<?php

namespace tests\ClassRepository;

use PHPCD\Element\ClassInfo\ReflectionClassFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\PatternMatcher\PatternMatcher;
use Composer\Autoload\ClassLoader;
use Psr\Log\LoggerInterface;
use PHPCD\PHPFile\PHPFileFactory;
use PHPCD\Filter\ClassFilter;
use PHPCD\Element\ClassInfo\ComposerClassmapFileRepository;
use PHPCD\Element\ClassInfo\ClassCollection;
use PHPCD\PHPFile\PHPFile;
use Mockery;

class ComposerClassmapFileRepositoryTest extends MockeryTestCase
{
    /**
     * Try to find class info of class that is invalid
     */
    public function testTryToFindNotValidClass()
    {
        $classLoader        = Mockery::mock(ClassLoader::class);
        $logger             = Mockery::mock(LoggerInterface::class);
        $patternMatcher     = Mockery::mock(PatternMatcher::class);
        $fileFactory        = Mockery::mock(PHPFileFactory::class);
        $file               = Mockery::mock(PHPFile::class);
        $classInfoFactory   = new ReflectionClassFactory();

        $classMap = [
            'Any\\Class' => 'ExampleFile.php'
        ];
        $classLoader->shouldReceive('getClassMap')->andReturn($classMap);

        $repository         = new ComposerClassmapFileRepository(
            $classLoader,
            $patternMatcher,
            $classInfoFactory,
            $fileFactory,
            $logger
        );
        $this->assertInstanceOf(ComposerClassmapFileRepository::class, $repository);

        $patternMatcher->shouldReceive('match')->times(count($classMap))->andReturn(true);

        $classLoader->shouldReceive('findFile')->with(key($classMap))->once()->andReturn(current($classMap));

        $fileFactory->shouldReceive('createFile')->once()->andReturn($file);
        $file->shouldReceive('hasErrors')->once()->andReturn(true);

        $logger->shouldReceive('warning')->once()->andReturnNull();

        $file->shouldReceive('getType')->once()->andReturn('class');
        $file->shouldReceive('getErrors')->once()->andReturn(['Some syntax error']);

        $collection = $repository->find(new ClassFilter([], key($classMap)));
        $this->assertInstanceOf(ClassCollection::class, $collection);
        $this->assertTrue($collection->isEmpty());
    }
}
