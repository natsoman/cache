<?php

namespace  Epignosis\Interfaces;

interface CompressorInterface {

    /**
     * @param mixed $value
     * @return mixed
     */
    public function compress($value);

    /**
     * @param mixed $value
     * @return mixed
     */
    public function uncompress($value);
}