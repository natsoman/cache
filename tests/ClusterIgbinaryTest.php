<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Epignosis\Serializers\Igbinary;
use Epignosis\{
    KeyBuilder,
    Client,
    RedisCache
};

final class ClusterIgbinaryTest extends TestCase
{
    public function testCanConnectOnCluster(): void
    {
        $cache = new RedisCache([
            'host' => [
                'redis-cluster:7000',
                'redis-cluster:7001',
                'redis-cluster:7002',
                'redis-cluster:7003',
                'redis-cluster:7004',
                'redis-cluster:7005'
            ]
        ]);

        $keyBuilder = new KeyBuilder(
            [
                'masterDomain' => function ($id = 78) { return sprintf('Domain:%s',$id); },
                'domainConfiguration' => function ($id = 78) { return sprintf('Domain:%s:Config:%s',$id,$id); },
                'session' => function ($ws = 3, $id = 99) { return sprintf('Session:%s-%s', $ws, $id); }
            ]
        );
        
        $client = new Client($cache, new Igbinary(),$keyBuilder);

        $key = 'domainConfiguration';
        $value = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.';

        $client->set($key, $value);
        $cachedValue = $client->get($key, function () { return null; });
        
        $this->assertSame($value, $cachedValue);
        
        $client->delete($key);
    }
}