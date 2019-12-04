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

        $this->keyBuilderMock->expects($this->any())->method('build')->will($this->returnArgument(0));

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
            'string' => ['value', 'stringKey', 's:5:"value";', 'x�+�2�R*K�)MU�\0008�'],
            'emptyString' => ['', 'emptyStringKey', 's:0:"";', 'x�+�2�RR�\000E�'],
            'null' => [null, 'nullKey', 'N;', 'x��\000\000�\000�'],
            'false' => [false, 'falseKey', 'b:0;', 'x�K�2�\000'],
            'object' => [new stdClass(), 'objectKey', 'O:8:"stdClass":0:{}', 'x�󷲰R*.Iq�I,.V�2���\000:F'],
            'int' => [1, 'intKey', 'i:1;', 'x�˴2�\000�'],
            'double' => [0.11, 'doubleKey', 'd:0.11;', 'x�K�2�34�\000��'],
            'nestedArray' => [
                ['a' => 3, 'b' => 2, ['aa' => 22]],
                'nestedArrayKey',
                'a:3:{s:1:"a";i:3;s:1:"b";i:2;i:0;a:1:{s:2:"aa";i:22;}}',
                'x�K�2��.�2�RJT�δ2���@l# 6�N����*���kk�8�'
            ],
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
                [
                    'testMixedNestedKey0',
                    'testMixedNestedKey1',
                    'testMixedNestedKey2',
                    'testMixedNestedKey3',
                    'testMixedNestedKey4'
                ]
            ]
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testSet($value, $key, $serializedValue, $compressedValue)
    {
        $this->serializerMock->expects($this->once())->method('serialize')->willReturn($serializedValue);
        $this->compressorMock->expects($this->once())->method('compress')->willReturn($compressedValue);
        $this->cacheMock->expects($this->once())->method('set')->willReturn(true);

        $this->assertSame(true, $this->client->set($key, $value));
    }

    /**
     * @dataProvider provider
     */
    public function testGet($value, $key, $serializedValue, $compressedValue)
    {
        $this->serializerMock->expects($this->once())->method('deserialize')->willReturn($value);
        $this->compressorMock->expects($this->once())->method('uncompress')->willReturn($serializedValue);
        $this->cacheMock->expects($this->once())->method('get')->willReturn($compressedValue);

        $actualValue = $this->client->get($key);
        $this->assertEquals($value, $actualValue);
    }
//
//	/**
//     * @dataProvider provider
//	 * @depends testConnection
//	 * @depends testSet
//	 */
//	public function testHas($value, $key, $client)
//	{
//		$this->assertSame(true, $client->has($key));
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