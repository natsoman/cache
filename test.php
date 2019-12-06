<?php

$v = 'value2';

$vs = serialize($v);
$gs = gzcompress($vs);

var_dump($vs);
var_dump($gs);