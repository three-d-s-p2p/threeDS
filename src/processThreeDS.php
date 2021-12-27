<?php

namespace Larangogon\ThreeDS;

use Larangogon\ThreeDS\Concrete\ThreeDSConcrete;
use Larangogon\ThreeDS\Traits\ProcessableTrait;
use Exception;

class processThreeDS
{
    use ProcessableTrait;

    /**
     * @param $data
     * @param string $emailName
     * @return void
     * @throws Exception
     */
    public function createRequest($data, string $emailName)
   {
       $threeDS = new ThreeDSConcrete();
       $threeDS->process($data,$emailName);
   }

}