<?php

namespace Larangogon\ThreeDS\Contracts;

interface ProcessableContract
{
    public function process($data, string $emailName, string $token): void;
}
