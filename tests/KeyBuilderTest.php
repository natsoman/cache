<?php

namespace Natso\Tests;

use Natso\KeyBuilder;
use PHPUnit\Framework\TestCase;

final class KeyBuilderTest extends TestCase
{
    public function mapProvider() {
        return [
            [
                [
                    // static key
                    'masterDomain' => 'Master',
                    // dynamic key / closure
                    'domainConfiguration' => function ($id = 1) { return sprintf('Domain:%s:Config', $id); },
                    // dynamic key / sprintf
                    'domainConfig' => ['Domain:%s:Config:%s', [1, 3]],
                ]
            ],
        ];
    }

    public function dataProvider()
    {
        return [
            'static key' => ['masterDomain', 'Master'],
            'closure key' => ['domainConfiguration-1', 'Domain:1:Config'],
            'array key' => ['domainConfig' => 'Domain:1:Config:3']
        ];
    }

    /**
     * @dataProvider
     */
	public function testInstantiation($map)
    {
        $keyBuilder = new KeyBuilder($map);
        return $keyBuilder;
    }

    /**
     * @dataProvider dataProvider
     * @depends testInstantiation
     */
    public function testBuild($key, $expected, $keyBuilder)
    {
        $this->assertEquals($expected, $keyBuilder->build($key));
    }
}