A flexible PSR-16 decorator.

## Use

### Prepare dependencies
\Psr\SimpleCache\CacheInterface

```
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

$adapter = new \Natso\Adapter\RedisAdapter($service);
```

\Natso\Serializer\SerializerInterface
```
$serializer = new \Natso\Serializer\NativeSerializer();
``` 

\Natso\KeyBuilder\KeyBuilderInterface
```

$map = [
    'staticKey'     => 'staticCacheKey',
    'uniqueKey'     => sprintf('uniqueCacheKey:%s', $_GET['id']),
    'closureKey'    => function () { return 'something that change in the runtime'; }
];

$keyBuilder = new \Natso\KeyBuilder\SimpleKeyBuilder($map);
``` 

\Natso\Compressor\CompressorInterface
```
$compressor = new \Natso\Compressor\ZlibCompressor();
``` 
### Inject dependencies on the wrapper
```
$cache = new \Natso\Cache(
    $adapter,
    $serializer,
    $keyBuilder,
    $compressor,
    ['namespace' => 'Example', 'ttl' => 3600]
);

$key = '101';
$cache->set($key,101);
$cache->has($key);
$cache->get($key);
$cache->delete($key);

$keys = ['key0', 'key1', 'key2'];
$cache->setMultiple(['key0' => null, 'key1' => 101, 'key2' => new stdClass()]);
$cache->getMultiple($keys);
$cache->deleteMultiple($keys);
```

## Memoization
In order to reduce the use of caching service, we preserve an associative array which represents an extra caching layer.