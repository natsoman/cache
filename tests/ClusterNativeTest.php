<?php

use RedisCluster;
use PHPUnit\Framework\TestCase;
use Epignosis\Serializers\Native;
use Epignosis\{
    KeyBuilder,
    Client
};

final class ClusterTest extends TestCase
{
    public function testCanConnectOnCluster(): void
    {
        $service = new RedisCluster(
            null,
            [
                'redis-cluster:7000',
                'redis-cluster:7001',
                'redis-cluster:7002',
                'redis-cluster:7003',
                'redis-cluster:7004',
                'redis-cluster:7005'
            ]
        );

        $keyBuilder = new KeyBuilder(
            [
                'masterDomain' => function ($id = 0) { return sprintf('Domain:%s',$id); },
                'domainConfiguration' => function ($id = 0) { return sprintf('Domain:%s:Config:%s',$id,$id); },
                'session' => function ($ws = 0, $id = 0) { return sprintf('Session:%s-%s', $ws, $id); }
            ]
        );

        $client = new Client(
            new Epignosis\Adapters\Redis($service),
            new Native(),
            $keyBuilder,
            new Epignosis\Compressors\Zlib(6)
        );

        $key = 'masterDomain';
        $value = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the
         industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and 
         scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into 
         electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of 
         Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus 
         PageMaker including versions of Lorem Ipsum.';

        $cached = $client->set($key, $value);
        $cachedValue = $client->get($key);
        $this->assertSame($value, $cachedValue);
        $deleted = $client->delete($key);

        unset($cached);
        unset($deleted);

        $values = $keys = [];
        for ($i = 0; $i < 200000; $i++) {
            $key = $client->getKeyBuilder()->build('domainConfiguration',$i);
            $keys[] = $key;
            $values[$key] = $value;
        }

        unset($key);

        $client->mSet($values);
        $cachedValues = $client->mGet($keys);
        $this->assertSame(array_values($values), $cachedValues);
        $client->mDelete($keys);

        unset($cachedValues);
        unset($keys);
    }
}