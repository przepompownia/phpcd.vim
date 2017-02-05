<?php

namespace tests\FunctionRepository;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\Element\FunctionInfo\FunctionCollection;
use PHPCD\Filter\FunctionFilter;
use PHPCD\Element\FunctionInfo\RuntimeFunctionRepository;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\Element\FunctionInfo\FunctionInfoFactory;
use PHPCD\Element\FunctionInfo\FunctionInfo;
use Mockery;

class RuntimeFunctionRepositoryTest extends MockeryTestCase
{
    /**
     * @test
     */
    public function find()
    {
        $patternMatcher    = Mockery::mock(PatternMatcher::class);
        $functionInfoFactory = Mockery::mock(FunctionInfoFactory::class);
        $repository = new RuntimeFunctionRepository($patternMatcher, $functionInfoFactory);
        $filter = Mockery::mock(FunctionFilter::class);
        $collection = Mockery::mock(FunctionCollection::class);

        $functionInfoFactory->shouldReceive('createFunctionCollection')->andReturn($collection);
        $functionInfo = Mockery::mock(FunctionInfo::class);
        $functionInfo->shouldReceive('getName')->andReturn('array_column');
        $functionInfoFactory->shouldReceive('createFunctionInfo')->andReturn($functionInfo);
        $pattern = 'arcol';
        $filter->shouldReceive('getPattern')->andReturn($pattern);

        $patternMatcher
            ->shouldReceive('match')
            ->with($pattern, Mockery::not('array_column'))
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $patternMatcher->shouldReceive('match')->with($pattern, 'array_column')->once()->andReturn(true);

        $collection->shouldReceive('add')->once();
        $repository->find($filter);
    }
}
