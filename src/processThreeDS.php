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
     * @param $token
     * @return void
     */
    public function createRequest($data, string $emailName, $token)
   {
       $threeDS = new ThreeDSConcrete();
       $threeDS->process($data,$emailName, $token);
   }

}