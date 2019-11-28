<?php

declare(strict_types=1);

namespace Epignosis\Tests;

use Epignosis\KeyBuilder;
use PHPUnit\Framework\TestCase;

final class RedisClusterTest extends TestCase
{
	public function provider()
	{
		return [
			[
				'string' => ['testValue'],
				'null' => [null],
				'false' => [false],
				'true' => [true],
				'object' => [new \stdClass()],
				'int' => [1],
				'nestedArrays' => [[[],[]]],
				'closure' => [function() {return false;}]
			],
			[
				'test'
			]
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

		$keyBuilder = new KeyBuilder(
			[
				'masterDomain' => function ($id = 0) { return sprintf('Domain:%s',$id); },
				'domainConfiguration' => function ($id = 0) { return sprintf('Domain:%s:Config',$id); },
				'session' => function ($ws = 0, $id = 0) { return sprintf('Session:%s-%s', $ws, $id); }
			]
		);

		$client = new \Epignosis\Client(
			new \Epignosis\Adapters\Redis($service),
			new \Epignosis\Serializers\Native(),
			$keyBuilder,
			new \Epignosis\Compressors\Zlib(6)
		);

		$this->assertSame(\Epignosis\Client::class,get_class($client));

		return $client;
	}

	/**
	 * @depends testConnection
	 * @dataProvider provider
	 */
	public function testSet($client, $value, $key)
	{
		$set = $client->set($key, $value);
		$this->assertSame(true, $set, 'SET operation failure');
	}

	/**
	 * @depends testConnection
	 * @depends testSet
	 * @dataProvider keyProvider
	 */
	public function testGet($client, $cachedValue, $key)
	{
		$get = $client->get($key);
		$this->assertSame($cachedValue, $get, 'GET operation failure');
	}

	/**
	 * @depends testConnection
	 * @depends testSet
	 * @dataProvider keyProvider
	 */
	public function testHas($client, $set, $key)
	{
		$has = $client->has($key);
		$this->assertSame($set, $has, 'HAS operation failure');
	}

	/**
	 * @depends testConnection
	 * @dataProvider keyProvider
	 */
	public function testDelete($client, $key)
	{
		$delete = $client->delete($key);
		$this->assertSame(true, $delete, 'DELETE operation failure');
	}

	/**
	 * @depends testConnection
	 * @depends testDelete
	 */
	public function testHasNot($client)
	{
		$has = $client->has($this->getKey());
		$this->assertSame(false, $has, 'HAS not operation failure');
	}

	/**
	 * @depends testConnection
	 */
	public function testSetMultiple($client)
	{
		$mSet = $client->mSet($values);
		$this->assertSame(true, $mSet, 'mSet operation failure');
	}

	/**
	 * @depends testConnection
	 * @depends testSetMultiple
	 */
	public function testGetMultiple($client)
	{
		$mGet = $client->mGet($keys);
		$this->assertSame($values, $mGet, 'mGet operation failure');
	}

	/**
	 * @depends testConnection
	 * @depends testGetMultiple
	 */
	public function deleteMultiple($client)
	{
		$client->mDelete($keys);
		$this->assertSame(true, $mSet, 'mDelete operation failure');
	}
}