<?php

namespace Illuminate\Tests\Cache;

use Illuminate\Cache\ArrayStore;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Cache\CacheManager;

class CacheManagerTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testCustomDriverClosureBoundObjectIsCacheManager()
    {
        $cacheManager = new CacheManager([
            'config' => [
                'cache.stores.'.__CLASS__ => [
                    'driver' => __CLASS__,
                ],
            ],
        ]);
        $driver = function () {
            return $this;
        };
        $cacheManager->extend(__CLASS__, $driver);
        $this->assertEquals($cacheManager, $cacheManager->store(__CLASS__));
    }

    public function testForgetCache()
    {
        $cacheManager = m::mock(CacheManager::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $cacheManager->shouldReceive('resolve')
            ->withArgs(['array'])
            ->times(4)
            ->andReturn(new ArrayStore());

        $cacheManager->shouldReceive('getDefaultDriver')
            ->once()
            ->andReturn('array');

        foreach (['array', ['array'], null] as $option) {
            $cacheManager->store('array');
            $cacheManager->store('array');
            $cacheManager->forgetCache($option);
            $cacheManager->store('array');
            $cacheManager->store('array');
        }
    }

    public function testForgetCacheForgets()
    {
        $cacheManager = new CacheManager([
            'config' => [
                'cache.stores.forget' => [
                    'driver' => 'forget',
                ],
            ],
        ]);
        $cacheManager->extend('forget', function() {
            return new ArrayStore();
        });

        $cacheManager->store('forget')->forever('foo', 'bar');
        $this->assertSame('bar', $cacheManager->store('forget')->get('foo'));
        $cacheManager->forgetCache('forget');
        $this->assertNull($cacheManager->store('forget')->get('foo'));
    }
}
