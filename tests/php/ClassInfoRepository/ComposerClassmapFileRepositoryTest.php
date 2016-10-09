<?php

namespace PHPCD\ClassInfo;

use PHPUnit\Framework\TestCase;
use PHPCD\PatternMatcher\PatternMatcher;
use Composer\Autoload\ClassLoader;
use Psr\Log\LoggerInterface;
use PHPCD\PHPFileInfo\PHPFileInfoFactory;
use PHPCD\ClassInfo\ClassInfoFactory;
use PHPCD\Filter\ClassFilter;
use PHPCD\ClassInfo\ComposerClassmapFileRepository;
use PHPCD\ClassInfo\ClassInfoCollection;
use PHPCD\PHPFileInfo\PHPFileInfo;
use Mockery;

class ComposerClassmapFileRepositoryTest extends TestCase
{
    /**
     * Try to find class info of class that is invalid
     */
    public function testTryToFindNotValidClass()
    {
        $classLoader        = Mockery::mock(ClassLoader::class);
        $logger             = Mockery::mock(LoggerInterface::class);
        $pattern_matcher    = Mockery::mock(PatternMatcher::class);
        $fileInfoFactory    = Mockery::mock(PHPFileInfoFactory::class);
        $fileInfo           = Mockery::mock(PHPFileInfo::class);
        $classInfoFactory   = new ClassInfoFactory($pattern_matcher);

        $classMap = [
            'Any\\Class' => 'ExampleFile.php'
        ];
        $classLoader->shouldReceive('getClassMap')->andReturn($classMap);

        $repository         = new ComposerClassmapFileRepository(
            $classLoader,
            $pattern_matcher,
            $classInfoFactory,
            $fileInfoFactory,
            $logger
        );
        $this->assertInstanceOf(ComposerClassmapFileRepository::class, $repository);

        $pattern_matcher->shouldReceive('match')->times(count($classMap))->andReturn(true);

        $classLoader->shouldReceive('findFile')->with(key($classMap))->once()->andReturn(current($classMap));

        $fileInfoFactory->shouldReceive('createFileInfo')->once()->andReturn($fileInfo);
        $fileInfo->shouldReceive('hasErrors')->once()->andReturn(true);

        $logger->shouldReceive('warning')->once()->andReturnNull();

        $fileInfo->shouldReceive('getType')->once()->andReturn('class');
        $fileInfo->shouldReceive('getErrors')->once()->andReturn(['Some syntax error']);

        $collection = $repository->find(new ClassFilter([], key($classMap)));
        $this->assertInstanceOf(ClassInfoCollection::class, $collection);
        $this->assertTrue($collection->isEmpty());
    }
}
