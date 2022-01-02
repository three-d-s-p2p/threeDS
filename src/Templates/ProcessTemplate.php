<?php

namespace Larangogon\ThreeDS\Templates;

use Larangogon\ThreeDS\Contracts\ProcessableContract;

abstract class ProcessTemplate implements ProcessableContract
{
    abstract protected function authorization($data, string $emailName, string $token);
    abstract protected function chunkInputData($references, string $emailName, string $token);
    abstract protected function emailError($error, string $emailName);
    abstract protected function create($references, string $emailName, string $token);
    abstract protected function request($data, string $emailName, string $token);
    abstract protected function response($response, $data, int $size);
    abstract protected function arrayInsert(array $data, int $size);

    public function process($data, string $emailName, string $token): void
    {
        $this->authorization($data, $emailName, $token);
    }
}
