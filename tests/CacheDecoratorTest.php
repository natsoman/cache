<?php

namespace Natso\Tests;

use Natso\CacheDecorator;
use Natso\Interfaces\CompressorInterface;
use Natso\Interfaces\KeyBuilderInterface;
use Natso\Interfaces\SerializerInterface;
use Psr\SimpleCache\CacheInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

final class CacheDecoratorTest extends TestCase
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
        // mock dependencies
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->keyBuilderMock = $this->createMock(KeyBuilderInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->compressorMock = $this->createMock(CompressorInterface::class);

        $this->keyBuilderMock->expects($this->any())->method('build')->will($this->returnArgument(0));

        $this->client = new CacheDecorator(
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
                ['stringKey0' => 'value0', 'stringKey1' => 'value1', 'stringKey2' => 'value2'], // pair
                [ // serialization map
                    ['value0', 'value1', 'value2'],
                    ['s:6:"value0";', 's:6:"value1";', 's:6:"value2";']
                ],
                [ // compression map
                    ['s:6:"value0";', 's:6:"value1";', 's:6:"value2";'],
                    ['x�+�2�R*K�)M5P�\000/�', 'x�+�2�R*K�)M5T�\0002�', 'x�+�2�R*K�)M5R�\0005�']
                ]
            ],
//            'empty' => [
//                [null, ''],
//                ['testMultiNullKey0', 'testMultiNullKey1']
//            ],
//            'bool' => [
//                [false, false],
//                ['testMultiFalseKey0', 'testMultiFalseKey2']
//            ],
//            'object' => [
//                [new stdClass(), new stdClass()],
//                ['testMultiObjectKey0', 'testMultiObjectKey1']
//            ],
//            'int' => [
//                [0, 1, 2],
//                ['testMultiIntKey0', 'testMultiIntKey1', 'testMultiIntKey2']
//            ],
//            'float' => [
//                [0.0, 0.1, 0.3],
//                ['testMultiFloatKey0', 'testMultiFloatKey1', 'testMultiFloatKey2']
//            ],
//            'nestedArray' => [
//                [array('a' => 1, array('aa' => 11)), array('b' => 2, array('bb' => 22))],
//                ['testMultiNestedArrayKey10', 'testMultiNestedArrayKey1']
//            ],
//            'mixed' => [
//                [null, 'testValue', false, true, new stdClass()],
//                [
//                    'testMixedNestedKey0',
//                    'testMixedNestedKey1',
//                    'testMixedNestedKey2',
//                    'testMixedNestedKey3',
//                    'testMixedNestedKey4'
//                ]
//            ]
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

	/**
     * @dataProvider provider
	 */
	public function testHas($value, $key, $serializedValue, $compressedValue)
	{
        $this->cacheMock->expects($this->once())->method('has')->willReturn(true);
		$this->assertSame(true, $this->client->has($key));
	}


    /**
     * @dataProvider provider
     */
    public function testDelete($value, $key, $serializedValue, $compressedValue)
    {
        $this->cacheMock->expects($this->once())->method('delete')->willReturn(true);
        $this->assertSame(true, $this->client->delete($key));
    }

    /**
     * @dataProvider multiProvider
     */
	public function testSetMultiple($pair, $serializationMap, $compressionMap)
    {
        $this->serializerMock->expects($this->any())->method('serialize')->will($this->returnValueMap($serializationMap));
        $this->compressorMock->expects($this->any())->method('compress')->will($this->returnValueMap($compressionMap));
        $this->assertSame(true, $this->client->setMultiple($pair));
    }
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