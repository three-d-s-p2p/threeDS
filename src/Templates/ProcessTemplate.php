<?php

namespace Larangogon\ThreeDS\Templates;

use Larangogon\ThreeDS\Contracts\ProcessableContract;

abstract class ProcessTemplate implements ProcessableContract
{
    abstract protected function authorization($data,$emailName);
    abstract protected function chunkInputData($references, string $emailName);
    abstract protected function emailError($error, string $emailName);
    abstract protected function create($references, $emailName);
    abstract protected function request($data, string $emailName);
    abstract protected function response($response, $data, int $size);
    abstract protected function arrayInsert(array $data, int $size);

    public function process($data,$emailName): void
    {
        $this->authorization($data,$emailName);
    }
}