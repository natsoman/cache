<?php

declare(strict_types=1);

namespace Epignosis\Tests;

use Epignosis\KeyBuilder;
use PHPUnit\Framework\TestCase;

final class RedisClusterTest extends TestCase
{
	public function keyProvider()
	{
		return [
			['testKey']
		];
	}

	public function valueProvider()
	{
		return [
			['testValue']
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
	 * @dataProvider keyProvider
	 * @dataProvider valueProvider
	 */
	public function testSet($client, $key, $value)
	{
		$set = $client->set($key, $value);
		$this->assertSame(true, $set, 'Caching failed');
	}

	/**
	 * @depends testConnection
	 * @depends testSet
	 * @dataProvider keyProvider
	 */
	public function testGet($client, $cachedValue, $key)
	{
		$get = $client->get($key);
		$this->assertSame($cachedValue, $get, 'Cached value differs from the expected');
	}

	/**
	 * @depends testConnection
	 * @depends testSet
	 * @dataProvider keyProvider
	 */
	public function testHas($client, $set, $key)
	{
		$has = $client->has($key);
		$this->assertSame($set, $has, 'Key is still there');
	}

	/**
	 * @depends testConnection
	 * @dataProvider keyProvider
	 */
	public function testDelete($client, $key)
	{
		$delete = $client->delete($key);
		$this->assertSame(true, $delete, 'Delete action fail');
	}

	/**
	 * @depends testConnection
	 * @depends testDelete
	 */
	public function testHasNot($client)
	{
		$has = $client->has($this->getKey());
		$this->assertSame(false, $has, 'Key is still there');
	}

	/**
	 * @depends testConnection
	 */
	public function testSetMultiple($client)
	{
		$mSet = $client->mSet($values);
		$this->assertSame(true, $mSet, '[Multi] Keys cannot be cached');
	}

	/**
	 * @depends testConnection
	 * @depends testSetMultiple
	 */
	public function testGetMultiple($client)
	{
		$mGet = $client->mGet($keys);
		$this->assertSame($values, $mGet, 'Cached value differs from the expected');
	}

	/**
	 * @depends testConnection
	 * @depends testGetMultiple
	 */
	public function deleteMultiple($client)
	{
		$client->mDelete($keys);
		$this->assertSame(true, $mSet, '[Multi] Keys cannot be deleted');
	}
}