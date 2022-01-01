<?php

namespace Larangogon\ThreeDS;

use Larangogon\ThreeDS\Concrete\ThreeDSConcrete;
use Larangogon\ThreeDS\Traits\ProcessableTrait;

class processThreeDS
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

    public function update()
    {
        //Actualizacion de sucursales, no se evidencia proceso en documentacion
    }
}
