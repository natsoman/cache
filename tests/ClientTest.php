<?php

declare(strict_types=1);

namespace Epignosis\Tests;

use Epignosis\Client;
use Epignosis\KeyBuilder;
use Epignosis\Serializers\Native;
use Epignosis\Compressors\Zlib;
use Epignosis\Adapters\Redis;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
	public function provider()
	{
		return [
				'string' => ['testValue','testValueKey'],
				'null' => [null,'testNullKey'],
				'false' => [false,'testFalseKey'],
				'true' => [true,'testTrueKey'],
				'object' => [new \stdClass(),'testObjectKey'],
				'int' => [1,'testIntKey'],
                'float' => [0.1,'testFloatKey'],
                'nestedArray' => [[ 'a' => 3, 'b' => 2, ['aa' => 22]],'testNestedArrayKey'],
		];
	}

    public function multiProvider()
    {
        return [
            'string' => [['testValue0','testValue1','testValue2'],['testValueKey0','testValueKey1','testValueKey2']],
            'null' => [[null,null,null],['testMultiNullKey0','testMultiNullKey1','testMultiNullKey2']],
            'false' => [[false,false],['testMultiFalseKey0','testMultiFalseKey2']],
            'true' => [[true,true],['testMultiTrueKey0','testMultiTrueKey1']],
            'object' => [[new \stdClass(),new \stdClass()],['testMultiObjectKey0','testMultiObjectKey1']],
            'int' => [[0,1,2],['testMultiIntKey0','testMultiIntKey1','testMultiIntKey2']],
            'float' => [[0.0,0.1,0.3],['testMultiFloatKey0','testMultiFloatKey1','testMultiFloatKey2']],
            'nestedArray' => [[array('a' => 1, array('aa' => 11)), array('b' => 2, array('bb' => 22))], ['testMultiNestedArrayKey10','testMultiNestedArrayKey1']],
            'mixed' => [[null,'testValue',false,true,new \stdClass()],['testMixedNestedKey0','testMixedNestedKey1','testMixedNestedKey2','testMixedNestedKey3','testMixedNestedKey4']],
        ];
    }

	public function testConnection()
	{
		$cache = $this->createMock(Redis::class);
		$cache->expects($this->any())->method('get')->will($this->returnArgument(1));
		$cache->expects($this->any())->method('set')->willReturn(true);

		try {

			$keyBuilder = new KeyBuilder(
				[
					'masterDomain' => function ($id = 0) { return sprintf('Domain:%s',$id); },
					'domainConfiguration' => function ($id = 0) { return sprintf('Domain:%s:Config',$id); },
					'session' => function ($ws = 0, $id = 0) { return sprintf('Session:%s-%s', $ws, $id); }
				]
			);

			$client = new Client(
				$cache,
				new Native(),
				$keyBuilder,
				new Zlib()
			);

			$this->assertEquals(Client::class, get_class($client));

			return $client;

		} catch(\Throwable $t) {
			return null;
		}
	}

	/**
     * @dataProvider provider
	 * @depends testConnection
	 */
	public function testSet($value, $key, $client)
	{
		$this->assertSame(true, $client->set($key, $value), 'SET operation failure');
	}

	/**
     * @dataProvider provider
	 * @depends testConnection
	 * @depends testSet
	 */
	public function testGet($value, $key, $client)
	{
		$this->assertEquals($value, $client->get($key), 'GET operation failure');
	}

	/**
     * @dataProvider provider
	 * @depends testConnection
	 * @depends testSet
	 */
	public function testHas($value, $key, $client)
	{
		$this->assertSame(true, $client->has($key), 'HAS operation failure');
	}

	/**
     * @dataProvider provider
	 * @depends testConnection
	 */
	public function testDelete($value, $key, $client)
	{
		$this->assertSame(true, $client->delete($key), 'DELETE operation failure');
	}

	/**
     * @dataProvider provider
	 * @depends testConnection
	 * @depends testDelete
	 */
	public function testHasNot($value, $key, $client)
	{
		$this->assertSame(false, $client->has($key), 'HAS not operation failure');
	}

	/**
     * @dataProvider multiProvider
	 * @depends testConnection
	 */
	public function testSetMultiple($values, $keys, $client)
	{
        $pair = array_fill_keys($keys,$values);
		$this->assertSame(true, $client->mSet($pair), 'mSet operation failure');
	}

	/**
     * @dataProvider multiProvider
     * @depends testConnection
     * @depends testSetMultiple
	 */
	public function testGetMultiple($values, $keys, $client)
	{
        $pair = array_fill_keys($keys,$values);
		$this->assertEquals($pair, $client->mGet($keys), 'mGet operation failure');
	}

	/**
     * @dataProvider multiProvider
	 * @depends testConnection
	 * @depends testGetMultiple
	 */
	public function testDeleteMultiple($values, $keys, $client)
	{
		$this->assertSame(true, $client->mDelete($keys), 'mDelete operation failure');
	}
}