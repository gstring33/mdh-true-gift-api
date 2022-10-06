<?php

namespace App\Services;

use Karriere\JsonDecoder\JsonDecoder;

class Decoder
{
    /**
     * @param string $data
     * @param string $class
     * @return mixed|null
     */
    public function jsonDecode(string $data, string $class)
    {
        $jsonDecoder = new JsonDecoder();
        return $jsonDecoder->decode($data, $class);
    }
}