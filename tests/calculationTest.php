<?php

namespace Larangogon\ThreeDS\Tests;

use Larangogon\ThreeDS\processThreeDS;
use PHPUnit\Framework\TestCase;

class calculationTest extends TestCase
{
    /**
     * @test
     */
    public function sum()
    {
        $count = new processThreeDS();
        $sum = $count->sum(7,9);
        $this->assertSame(16, $sum);
    }
}