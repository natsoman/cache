# cache
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

\Natso\KeyBuilder\KeyBuilderInterface (optional)
```

$map = [
    'staticKey'     => 'staticCacheKey',
    'staticKey'     => sprintf('staticKey:%s', $_GET['id']),
    'closureKey'    => function () { return 'closureKey'; }
];

$keyBuilder = new \Natso\KeyBuilder\SimpleKeyBuilder($map);
``` 

\Natso\Compressor\CompressorInterface (optional)
```
$compressor = new \Natso\Compressor\ZlibCompressor();
``` 
### Inject dependencies on the wrapper
```
$cache = new \Natso\Cache(
    $adapter,
    $serializer,
    $keyBuilder,
    $compressor
);

$key = '101';
$cache->set('key',101);
$cache->has('key');
$cache->get('key');
$cache->delete('key');

$keys = ['key0', 'key1', 'key2'];
$cache->setMultiple(['key0' => null, 'key1' => 101, 'key2' => new stdClass()]);
$cache->getMultiple($keys);
$cache->deleteMultiple($keys);
$cache->getMultiple($keys);

```

## Memoization