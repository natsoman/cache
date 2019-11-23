<?php
namespace Epignosis;

use Epignosis\Interfaces\SerializerInterface;

class Native implements SerializerInterface {

    
    protected $normalizer;

    /**
     * @var null
     */
    protected $encoder;

    /**
     * @inheritdoc
     */
    public function __construct($normalizer = null, $encoder = null)
    {
        $this->normalizer = $normalizer;
        $this->encoder = $encoder;
    }

    /**
     * @inheritdoc
     */
    public function serialize($value):string
    {
        return \serialize($value);
    }

    /**
     * @inheritdoc
     */
    public function deserialize(string $value)
    {
        return \unserialize($value);
    }
}