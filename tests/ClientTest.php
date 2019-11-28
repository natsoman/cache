<?php

declare(strict_types=1);

namespace Epignosis\Tests;

use Epignosis\KeyBuilder;
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

        $masters = $service->_masters();

        $pings = 0;
        foreach ($masters as $master) {
            $pings += (int)$service->ping($master);
        }

        // all master are alive
        $this->assertSame($pings,count($masters));

		$keyBuilder = new KeyBuilder(
			[
				'masterDomain' => function ($id = 0) { return sprintf('Domain:%s',$id); },
				'domainConfiguration' => function ($id = 0) { return sprintf('Domain:%s:Config',$id); },
				'session' => function ($ws = 0, $id = 0) { return sprintf('Session:%s-%s', $ws, $id); }
			]
		);

		$client = new \Epignosis\Client(
			new \Epignosis\Adapters\Redis($service),
			new \Epignosis\Serializers\Igbinary(),
			$keyBuilder,
			new \Epignosis\Compressors\Zlib(6)
		);

		return $client;
	}

	/**
     * @dataProvider provider
	 * @depends testConnection
	 */
	public function testSet($value, $key, $client)
	{
		$set = $client->set($key, $value);
		$this->assertSame(true, $set, 'SET operation failure');
	}

	/**
     * @dataProvider provider
	 * @depends testConnection
	 * @depends testSet
	 */
	public function testGet($value, $key, $client)
	{
		$get = $client->get($key);
		$this->assertEquals($value, $get, 'GET operation failure');
	}

	/**
     * @dataProvider provider
	 * @depends testConnection
	 * @depends testSet
	 */
	public function testHas($value, $key, $client)
	{
		$has = $client->has($key);
		$this->assertSame(true, $has, 'HAS operation failure');
	}

	/**
     * @dataProvider provider
	 * @depends testConnection
	 */
	public function testDelete($value, $key, $client)
	{
		$delete = $client->delete($key);
		$this->assertSame(true, $delete, 'DELETE operation failure');
	}

	/**
     * @dataProvider provider
	 * @depends testConnection
	 * @depends testDelete
	 */
	public function testHasNot($value, $key, $client)
	{
		$has = $client->has($key);
		$this->assertSame(false, $has, 'HAS not operation failure');
	}

	/**
     * @dataProvider multiProvider
	 * @depends testConnection
	 */
	public function testSetMultiple($values, $keys, $client)
	{
        $pair = array_fill_keys($keys,$values);
		$mSet = $client->mSet($pair);
		$this->assertSame(true, $mSet, 'mSet operation failure');
	}

	/**
     * @dataProvider multiProvider
     * @depends testConnection
     * @depends testSetMultiple
	 */
	public function testGetMultiple($values, $keys, $client)
	{
        $pair = array_fill_keys($keys,$values);
		$mGet = $client->mGet($keys);
		$this->assertEquals($mGet, $pair, 'mGet operation failure');
	}

	/**
     * @dataProvider multiProvider
	 * @depends testConnection
	 * @depends testGetMultiple
	 */
	public function testDeleteMultiple($values, $keys, $client)
	{
		$mDelete = $client->mDelete($keys);
		$this->assertSame(true, $mDelete, 'mDelete operation failure');
	}
}