<?php

require 'vendor/autoload.php';

try {

    $service = new \RedisCluster(
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

    $cache = new \Natso\Adapter\RedisAdapter($service);

} catch (\RedisClusterException | \Natso\Exception\CacheException $cacheException) {
    exit(0);
}

$keyBuilderMap = [
    'staticKey' => 'staticCacheKey',
    'uniqueKey' => sprintf('uniqueCacheKey-%s', 1),
    'closureKey' => function () {
        return 'test';
    } // use closure to build keys that could be change in the runtime
];

$serializer = new \Natso\Serializer\NullSerializer();
$compressor = new \Natso\Compressor\NullCompressor();
$keyBuilder = new \Natso\KeyBuilder\SimpleKeyBuilder($keyBuilderMap);
$cache = new \Natso\Cache($cache, $serializer, $keyBuilder, $compressor, 'Example', 8400);

try {

    $set = $cache->set('key', 101);
    $has = $cache->has('key');
    $get = $cache->get('key');
    $delete = $cache->delete('key');
    $has = $cache->has('key');

    $keys = ['key0', 'key1', 'key2'];
    $setMultiple = $cache->setMultiple(['key0' => null, 'key1' => 101, 'key2' => new stdClass()]);
    $getMultiple = $cache->getMultiple($keys);
    $deleteMultiple = $cache->deleteMultiple($keys);
    $getMultiple = $cache->getMultiple($keys);

} catch (\Psr\SimpleCache\InvalidArgumentException $e) {
    // ...
}

die();