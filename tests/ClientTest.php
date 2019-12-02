<?php

declare(strict_types=1);

namespace Epignosis\Tests;

use Epignosis\Client;
use Epignosis\Interfaces\ClientInterface;
use Epignosis\Interfaces\CompressorInterface;
use Epignosis\Interfaces\KeyBuilderInterface;
use Epignosis\Interfaces\SerializerInterface;
use Psr\SimpleCache\CacheInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ClientTest extends TestCase
{
    /**
     * @var CacheInterface
     */
    protected $cacheMock;

    /**
     * @var KeyBuilderInterface
     */
    protected $keyBuilderMock;

    /**
     * @var SerializerInterface
     */
    protected $serializerMock;

    /**
     * @var CompressorInterface
     */
    protected $compressorMock;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        // mock dependencies
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->keyBuilderMock = $this->createMock(KeyBuilderInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->compressorMock = $this->createMock(CompressorInterface::class);

        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->will($this->returnCallback(function ($v) {
                return serialize($v);
            }));

        $this->serializerMock->expects($this->any())
            ->method('deserialize')
            ->will($this->returnCallback(function ($v) {
                return unserialize($v);
            }));

        $this->compressorMock->expects($this->any())
            ->method('compress')
            ->will($this->returnCallback(function ($v) {
                return gzcompress($v);
            }));

        $this->compressorMock->expects($this->any())
            ->method('uncompress')
            ->will($this->returnCallback(function ($v) {
                return gzuncompress($v);
            }));

        $this->keyBuilderMock->expects($this->any())
            ->method('build')
            ->will($this->returnArgument(0));

        $this->client = new Client(
            $this->cacheMock,
            $this->serializerMock,
            $this->keyBuilderMock,
            $this->compressorMock
        );

        parent::__construct($name, $data, $dataName);
    }

    public function provider()
    {
        return [
            'string' => ['testValue', 'testValueKey'],
            'null' => [null, 'testNullKey'],
            'false' => [false, 'testFalseKey'],
            'true' => [true, 'testTrueKey'],
            'object' => [new stdClass(), 'testObjectKey'],
            'int' => [1, 'testIntKey'],
            'float' => [0.1, 'testFloatKey'],
            'nestedArray' => [['a' => 3, 'b' => 2, ['aa' => 22]], 'testNestedArrayKey'],
        ];
    }

    public function multiProvider()
    {
        return [
            'string' => [
                ['testValue0', 'testValue1', 'testValue2'],
                ['testValueKey0', 'testValueKey1', 'testValueKey2']
            ],
            'empty' => [
                [null, ''],
                ['testMultiNullKey0', 'testMultiNullKey1']
            ],
            'bool' => [
                [false, false],
                ['testMultiFalseKey0', 'testMultiFalseKey2']
            ],
            'object' => [
                [new stdClass(), new stdClass()],
                ['testMultiObjectKey0', 'testMultiObjectKey1']
            ],
            'int' => [
                [0, 1, 2],
                ['testMultiIntKey0', 'testMultiIntKey1', 'testMultiIntKey2']
            ],
            'float' => [
                [0.0, 0.1, 0.3],
                ['testMultiFloatKey0', 'testMultiFloatKey1', 'testMultiFloatKey2']
            ],
            'nestedArray' => [
                [array('a' => 1, array('aa' => 11)), array('b' => 2, array('bb' => 22))],
                ['testMultiNestedArrayKey10', 'testMultiNestedArrayKey1']
            ],
            'mixed' => [
                [null, 'testValue', false, true, new stdClass()],
                ['testMixedNestedKey0', 'testMixedNestedKey1', 'testMixedNestedKey2', 'testMixedNestedKey3', 'testMixedNestedKey4']
            ]
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testSet($value, $key)
    {
        $this->cacheMock->expects($this->once())->method('set')->willReturn(true);
        $this->assertSame(true, $this->client->set($key, $value));
    }

    /**
     * @dataProvider provider
     */
    public function testGet($value, $key)
    {
        $this->cacheMock->expects($this->once())->method('get')->willReturn($value);
        $this->assertEquals($value, $this->client->get($key));
    }
//
//	/**
//     * @dataProvider provider
//	 * @depends testConnection
//	 * @depends testSet
//	 */
//	public function testHas($value, $key, $client)
//	{
//		$this->assertSame(true, $client->has($key), 'HAS operation failure');
//	}
//
//	/**
//     * @dataProvider provider
//	 * @depends testConnection
//	 */
//	public function testDelete($value, $key, $client)
//	{
//		$this->assertSame(true, $client->delete($key), 'DELETE operation failure');
//	}
//
//	/**
//     * @dataProvider provider
//	 * @depends testConnection
//	 * @depends testDelete
//	 */
//	public function testHasNot($value, $key, $client)
//	{
//		$this->assertSame(false, $client->has($key), 'HAS not operation failure');
//	}
//
//	/**
//     * @dataProvider multiProvider
//	 * @depends testConnection
//	 */
//	public function testSetMultiple($values, $keys, $client)
//	{
//        $pair = array_fill_keys($keys,$values);
//		$this->assertSame(true, $client->mSet($pair), 'mSet operation failure');
//	}
//
//	/**
//     * @dataProvider multiProvider
//     * @depends testConnection
//     * @depends testSetMultiple
//	 */
//	public function testGetMultiple($values, $keys, $client)
//	{
//        $pair = array_fill_keys($keys,$values);
//		$this->assertEquals($pair, $client->mGet($keys), 'mGet operation failure');
//	}
//
//	/**
//     * @dataProvider multiProvider
//	 * @depends testConnection
//	 * @depends testGetMultiple
//	 */
//	public function testDeleteMultiple($values, $keys, $client)
//	{
//		$this->assertSame(true, $client->mDelete($keys), 'mDelete operation failure');
//	}
}