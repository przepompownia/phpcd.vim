<?php

namespace tests\ConstantInfo;

use PHPCD\Element\ConstantInfo\ConstantCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\Element\ConstantInfo\RuntimeConstantRepository;
use PHPCD\Filter\ConstantFilter;
use PHPCD\Element\ConstantInfo\ConstantFactory;
use Mockery;

class RuntimeConstantRepositoryTest extends MockeryTestCase
{
    /**
     * @test
     */
    public function find()
    {
        $patternMatcher    = Mockery::mock(PatternMatcher::class);
        $constantFactory = Mockery::mock(ConstantFactory::class);
        $collection = Mockery::mock(ConstantCollection::class);
        $filter = Mockery::mock(ConstantFilter::class);

        $repository = new RuntimeConstantRepository($patternMatcher, $constantFactory);
        $pattern = 'arrfilterkey';
        $filter->shouldReceive('getPattern')->andReturn($pattern);
        $constantFactory->shouldReceive('createConstantCollection')->andReturn($collection);

        $patternMatcher
            ->shouldReceive('match')
            ->with($pattern, Mockery::not('ARRAY_FILTER_USE_KEY'))
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $patternMatcher->shouldReceive('match')->with($pattern, 'ARRAY_FILTER_USE_KEY')->once()->andReturn(true);
        $collection->shouldReceive('add')->once();

        $repository->find($filter);
    }
}
