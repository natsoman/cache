<?php
namespace Epignosis\Interfaces;

interface SerializerInterface {

    /**
     * @param $normalizer
     * @param $encoder
     */
    public function __construct($normalizer, $encoder);

    /**
     * @param $value
     * @return string
     */
    public function serialize($value):string;

    /**
     * @param string $value
     * @return mixed
     */
    public function deserialize(string $value);
}