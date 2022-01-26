<?php

namespace Larangogon\ThreeDS\Tests;

use Larangogon\ThreeDS\Providers\AppServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            AppServiceProvider::class,
        ];
    }
}
