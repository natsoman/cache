<?php

namespace Natso\Tests;

use Natso\Cache;
use Natso\Compressor\CompressorInterface;
use Natso\KeyBuilder\KeyBuilderInterface;
use Natso\KeyBuilder\NullKeyBuilder;
use Natso\Serializer\SerializerInterface;
use Psr\SimpleCache\CacheInterface;
use PHPUnit\Framework\TestCase;

final class CacheTest extends TestCase
{
    protected const DATA_DIR = __DIR__ . '/./data/CacheTest/';

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var KeyBuilderInterface
     */
    protected $keyBuilder;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var CompressorInterface
     */
    protected $compressor;

    /**
     * @var CacheInterface
     */
    protected $client;

    /**
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->keyBuilder = $this->createMock(NullKeyBuilder::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->compressor = $this->createMock(CompressorInterface::class);

        $this->client = new Cache(
            $this->cache,
            $this->serializer,
            $this->keyBuilder,
            $this->compressor,
            ['namespace' => 'Test']
        );

        parent::__construct($name, $data, $dataName);
    }

    /**
     * @dataProvider provider
     */
    public function testSet($value, $key, $serializedValue, $compressedValue)
    {
        $this->serializer->expects($this->once())->method('serialize')->willReturn($serializedValue);
        $this->compressor->expects($this->once())->method('compress')->willReturn($compressedValue);
        $this->cache->expects($this->once())->method('set')->willReturn(true);
        $this->assertSame(true, $this->client->set($key, $value));
    }

    /**
     * @dataProvider provider
     */
    public function testGet($value, $key, $serializedValue, $compressedValue)
    {
        $this->serializer->expects($this->once())->method('deserialize')->willReturn($value);
        $this->compressor->expects($this->once())->method('uncompress')->willReturn($serializedValue);
        $this->cache->expects($this->once())->method('get')->willReturn($compressedValue);
        $actualValue = $this->client->get($key);
        $this->assertEquals($value, $actualValue);
    }

    /**
     * @dataProvider provider
     */
    public function testHas($value, $key, $serializedValue, $compressedValue)
    {
        $this->cache->expects($this->once())->method('has')->willReturn(true);
        $this->assertSame(true, $this->client->has($key));
    }


    /**
     * @dataProvider provider
     */
    public function testDelete($value, $key, $serializedValue, $compressedValue)
    {
        $this->cache->expects($this->once())->method('delete')->willReturn(true);
        $this->assertSame(true, $this->client->delete($key));
    }

    /**
     * @dataProvider multiProvider
     */
    public function testSetMultiple($keyValue)
    {
        $this->serializer->expects($this->any())->method('serialize')->willReturnMap($this->getSerializationMap());
        $this->compressor->expects($this->any())->method('compress')->willReturnMap($this->getCompressionMap());
        $this->cache->expects($this->once())->method('setMultiple')->willReturn(true);
        $this->assertSame(true, $this->client->setMultiple($keyValue));
    }

    /**
     * @dataProvider multiProvider
     */
    public function testGetMultiple($keyValue, $cachedValues)
    {
        $this->serializer->expects($this->any())
            ->method('deserialize')
            ->willReturnMap($this->getSerializationMap(true));

        $this->compressor->expects($this->any())
            ->method('uncompress')
            ->willReturnMap($this->getCompressionMap(true));

        $this->cache->expects($this->once())->method('getMultiple')->willReturn($cachedValues);
        $this->assertEquals($keyValue, $this->client->getMultiple(array_keys($keyValue)));
    }

    /**
     * @dataProvider multiProvider
     */
    public function testDeleteMultiple($keyValue)
    {
        $this->cache->expects($this->once())->method('deleteMultiple')->willReturn(true);
        $this->assertSame(true, $this->client->deleteMultiple(array_keys($keyValue)));
    }

    public function provider()
    {
        return require static::DATA_DIR . 'provider.php';
    }

    public function multiProvider()
    {
        return require static::DATA_DIR . 'multi-provider.php';
    }

    protected function getSerializationMap($flip = false)
    {
        if (!$flip) {
            return require static::DATA_DIR . 'serialization-map.php';
        } else {
            return require static::DATA_DIR . 'serialization-map-flip.php';
        }
    }

    protected function getCompressionMap($flip = false)
    {
        if (!$flip) {
            return require static::DATA_DIR . 'compression-map.php';
        } else {
            return require static::DATA_DIR . 'compression-map-flip.php';
        }
    }
}