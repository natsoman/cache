<?php

require 'vendor/autoload.php';

$client = new \Epignosis\CacheDecorator(['host' => [
        'redis-cluster:7000',
        'redis-cluster:7001',
        'redis-cluster:7002',
        'redis-cluster:7003',
        'redis-cluster:7004',
        'redis-cluster:7005'
    ]]
);

echo $client->info();