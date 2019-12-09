<?php

namespace Natso\Tests;

use Natso\Cache;
use Natso\Compressor\CompressorInterface;
use Natso\KeyBuilder\KeyBuilderInterface;
use Natso\Serializer\SerializerInterface;
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
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->keyBuilderMock = $this->createMock(KeyBuilderInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->compressorMock = $this->createMock(CompressorInterface::class);

        $this->keyBuilderMock->expects($this->any())
            ->method('build')
            ->will($this->returnArgument(0));

        $this->client = new Cache(
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
            'emptyStrings' => ['      
            ', 'emptyStringKey', 's:0:"";', 'x�+�2�RR�\000E�'],
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
                [
                    'stringKey0' => 'value0',
                    'stringKey1' => 'value1',
                    'stringKey2' => 'value2'
                ],
                [
                    'stringKey0' => 'x�+�2�R*K�)M5P�\000/�',
                    'stringKey1' => 'x�+�2�R*K�)M5T�\0002�',
                    'stringKey2' => 'x�+�2�R*K�)M5R�\0005�'
                ],
            ],
            'empty' => [
                [
                    'emptyStringKey0' => '',
                    'emptyStringKey1' => '  ',
                ],
                [
                    'emptyStringKey0' => 'x�+�2�RR�\000E�',
                    'emptyStringKey1' => 'x�+�2�RRPP�\000
��',
                ],
            ],
            'bool' => [
                [
                    'boolKey0' => false,
                    'boolKey1' => true,
                ],
                [
                    'boolKey0' => 'x�K�2�\000',
                    'boolKey1' => 'x�K�2�\000�',
                ],
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
	public function testSetMultiple($keyValue)
    {
        $this->serializerMock->expects($this->any())->method('serialize')->willReturnMap($this->getSerializationMap());
        $this->compressorMock->expects($this->any())->method('compress')->willReturnMap($this->getCompressionMap());
        $this->cacheMock->expects($this->once())->method('setMultiple')->willReturn(true);
        $this->assertSame(true, $this->client->setMultiple($keyValue));
    }

	/**
     * @dataProvider multiProvider
	 */
	public function testGetMultiple($keyValue, $cachedValues)
	{
        $this->serializerMock->expects($this->any())
            ->method('deserialize')
            ->willReturnMap($this->getSerializationMap(true));

        $this->compressorMock->expects($this->any())
            ->method('uncompress')
            ->willReturnMap($this->getCompressionMap(true));

        $this->cacheMock->expects($this->once())->method('getMultiple')->willReturn($cachedValues);
		$this->assertEquals($keyValue, $this->client->getMultiple(array_keys($keyValue)));
	}

    /**
     * @dataProvider multiProvider
     */
    public function testDeleteMultiple($keyValue)
    {
        $this->cacheMock->expects($this->once())->method('deleteMultiple')->willReturn(true);
        $this->assertSame(true, $this->client->deleteMultiple($keyValue));
    }

    protected function getSerializationMap($flip = false)
    {
        if (!$flip) {
            return [
                ['value0', 's:6:"value0";'],
                ['value1', 's:6:"value1";'],
                ['value2', 's:6:"value2";'],
                [false, 'b:0;'],
                [true, 'b:1;'],
                ['', 's:0:"";'],
                ['  ', 's:2:"  ";']
            ];
        } else {
            return [
                ['s:6:"value0";', 'value0'],
                ['s:6:"value1";', 'value1'],
                ['s:6:"value2";', 'value2'],
                ['b:0;', false],
                ['b:1;', true],
                ['s:0:"";', ''],
                ['s:2:"  ";', '  ']
            ];
        }
    }

    protected function getCompressionMap($flip = false)
    {
        if (!$flip) {
            return [
                ['s:6:"value0";', 'x�+�2�R*K�)M5P�\000/�'],
                ['s:6:"value1";', 'x�+�2�R*K�)M5T�\0002�'],
                ['s:6:"value2";', 'x�+�2�R*K�)M5R�\0005�'],
                ['b:0;', 'x�K�2�\000'],
                ['b:1;', 'x�K�2�\000�'],
                ['s:0:"";', 'x�+�2�RR�\000E�'],
                ['s:2:"  ";', 'x�+�2�RRPP�\000
��']
            ];
        } else {
            return [
                ['x�+�2�R*K�)M5P�\000/�', 's:6:"value0";'],
                ['x�+�2�R*K�)M5T�\0002�', 's:6:"value1";'],
                ['x�+�2�R*K�)M5R�\0005�', 's:6:"value2";'],
                ['x�K�2�\000', 'b:0;'],
                ['x�K�2�\000�', 'b:1;'],
                ['x�+�2�RR�\000E�', 's:0:"";'],
                ['x�+�2�RRPP�\000
��', 's:2:"  ";']
            ];
        }
    }
}