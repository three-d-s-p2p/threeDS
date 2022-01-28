<?php

namespace Larangogon\ThreeDS;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Larangogon\ThreeDS\Traits\ConnectionTrait;
use Psr\Http\Message\ResponseInterface;

class ProcessThreeDSCreateOrUpdate
{
    use ConnectionTrait;

    /**
     * @param $data
     * @param string $token
     * @return Response|ResponseInterface
     * @throws GuzzleException
     */
    public function createOrUpdate($data, string $token)
    {
        if ($data->merchantID = ! null) {
            return $this->requestConnectionUpdate($data, $token);
        } else {
            return $this->requestConnectionCreate($data, $token);
        }
    }
}
