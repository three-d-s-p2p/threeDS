<?php

namespace Larangogon\ThreeDS\Concrete;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Larangogon\ThreeDS\Templates\ProcessTemplate;
use Larangogon\ThreeDS\Traits\ConnectionTrait;
use Larangogon\ThreeDS\Traits\ProcessableTrait;
use Psr\Http\Message\ResponseInterface;

class ThreeDSUpdateConcrete extends ProcessTemplate
{
    use ProcessableTrait;
    use ConnectionTrait;

    /**
     * @param object $data
     * @param string $token
     * @return Response|ResponseInterface
     * @throws GuzzleException
     */
    public function request(object $data, string $token)
    {
        return $this->requestConnectionUpdate($data, $token);
    }

    /**
     * @param $references
     * @param string $token
     * @return void
     * @throws GuzzleException
     */
    public function create($references, string $token)
    {
        foreach ($references as $data) {
            $response = $this->request($data, $token);
            $this->responseUpdate($response);
        }
    }
}
