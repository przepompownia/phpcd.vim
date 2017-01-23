<?php

namespace tests\FunctionRepository;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\FunctionInfo\FunctionCollection;
use PHPCD\Filter\FunctionFilter;
use PHPCD\FunctionInfo\RuntimeFunctionRepository;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\FunctionInfo\FunctionInfoFactory;
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

        $functionInfoFactory->shouldReceive('createFunctionCollection')->andReturn(new FunctionCollection());
        $pattern = 'arcol';
        $filter->shouldReceive('getPattern')->andReturn($pattern);

        $patternMatcher
            ->shouldReceive('match')
            ->with(Mockery::type('string'), Mockery::type('string'))
            ->zeroOrMoreTimes()
            ->andReturn(false)
            ->byDefault();
        $patternMatcher->shouldReceive('match')->with($pattern, 'array_column')->once()->andReturn(true);

        $collection->shouldReceive('add')->once();
        $collection = $repository->find($filter);
    }
}
