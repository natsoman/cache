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

$serializer = new \Natso\Serializer\NativeSerializer();

$compressor = new \Natso\Compressor\DeflateCompressor(6);

$keyBuilderMap = [
    'staticKey' => 'staticCacheKey', // for shared keys beetween
    'uniqueKey' => sprintf('uniqueCacheKey-%s', 1), // use
    'closureKey' => function () { return 'test';} // use closure to build keys that could be change in the runtime
];

$keyBuilder = new \Natso\KeyBuilder\SimpleKeyBuilder($keyBuilderMap);

$cache = new \Natso\Cache(
    $cache,
    $serializer,
    $keyBuilder,
    $compressor
);

try {

    $set = $cache->set('staticKey',101);
    $has = $cache->has('staticKey');
    $get = $cache->get('staticKey');
    $delete = $cache->delete('staticKey');
    $has = $cache->has('staticKey');

    $keys = ['staticKey0', 'staticKey1', 'staticKey2'];
    $setMultiple = $cache->setMultiple(['staticKey0' => null, 'staticKey1' => 101, 'staticKey2' => new stdClass()]);
    $getMultiple = $cache->getMultiple($keys);
    $deleteMultiple = $cache->deleteMultiple($keys);
    $getMultiple = $cache->getMultiple($keys);

} catch (\Psr\SimpleCache\InvalidArgumentException $e) {
    // ...
}

die();
