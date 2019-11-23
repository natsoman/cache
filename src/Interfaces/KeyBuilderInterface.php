<?php
namespace  Epignosis\Interfaces;

interface KeyBuilderInterface {

    public function build(string $key);

    public function getMap():array;

    public function setMap(array $map);
}