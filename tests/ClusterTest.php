<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ClusterTest extends TestCase
{
    public function testCanConnectOnCluster(): void
    {
        $cache = new \Epignosis\Cache(['host' => [
            'redis-cluster:7000',
            'redis-cluster:7001',
            'redis-cluster:7002',
            'redis-cluster:7003',
            'redis-cluster:7004',
            'redis-cluster:7005'
        ]]);

        try {
            $cache->set('testKey', 'testValue', 3600);
            $this->assertSame(
                'testValue',
                $cache->get('testKey')
            );
        } catch (\RedisClusterException $e) {
            $this->expectOutputString($e->getMessage());
        }
    }
}
