<?php

namespace Larangogon\ThreeDS;

use Larangogon\ThreeDS\Concrete\ThreeDSConcrete;
use Larangogon\ThreeDS\Concrete\ThreeDSUpdateConcrete;
use Larangogon\ThreeDS\Traits\ProcessableTrait;

class ProcessThreeDSMassive
{
    use ProcessableTrait;

    /**
     * @param $data
     * @param string $emailName
     * @param string $token
     * @return void
     */
    public function createRequest($data, string $emailName, string $token)
    {
        $threeDS = new ThreeDSConcrete();
        $threeDS->process($data, $emailName, $token);
    }

    /**
     * @param $data
     * @param string $emailName
     * @param string $token
     * @return void
     */
    public function update($data, string $emailName, string $token)
    {
        $threeDS = new ThreeDSUpdateConcrete();
        $threeDS->process($data, $emailName, $token);
    }
}
