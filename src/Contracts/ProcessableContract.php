<?php

namespace Larangogon\ThreeDS\Contracts;

interface ProcessableContract
{
    public function process($data,$emailName): void;
}