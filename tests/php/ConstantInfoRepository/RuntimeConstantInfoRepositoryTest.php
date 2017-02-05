<?php

namespace tests\ConstantInfoRepository;

use PHPCD\Element\ConstantInfo\ConstantInfoCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\Element\ConstantInfo\RuntimeConstantInfoRepository;
use PHPCD\Filter\ConstantFilter;
use PHPCD\Element\ConstantInfo\ConstantInfoFactory;
use Mockery;

class RuntimeConstantInfoRepositoryTest extends MockeryTestCase
{
    /**
     * @test
     */
    public function find()
    {
        $patternMatcher    = Mockery::mock(PatternMatcher::class);
        $constantInfoFactory = Mockery::mock(ConstantInfoFactory::class);
        $collection = Mockery::mock(ConstantInfoCollection::class);
        $filter = Mockery::mock(ConstantFilter::class);

        $repository = new RuntimeConstantInfoRepository($patternMatcher, $constantInfoFactory);
        $pattern = 'arrfilterkey';
        $filter->shouldReceive('getPattern')->andReturn($pattern);
        $constantInfoFactory->shouldReceive('createConstantInfoCollection')->andReturn($collection);

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
