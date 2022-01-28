<?php

namespace Larangogon\ThreeDS\Concrete;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Larangogon\ThreeDS\Templates\ProcessTemplate;
use Larangogon\ThreeDS\Traits\ProcessableTrait;
use Psr\Http\Message\ResponseInterface;

class ThreeDSUpdateConcrete extends ProcessTemplate
{
    use ProcessableTrait;

    /**
     * @param object $data
     * @param string $emailName
     * @param string $token
     * @return Response|ResponseInterface
     * @throws GuzzleException
     */
    public function request(object $data, string $emailName, string $token)
    {
        try {
            return $this->getClient()->post(
                "https://3dss-test.placetopay.com/api/v1/{$data->merchantID}/branches",
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer {$token}"
                    ],
                    'json' => [
                        'branches' => [
                            'name' => $data->nameBranch,
                            'brand' => $data->brand,
                            'country' => $data->country,
                            'currency' => $data->currency,
                            'url' => $data->url
                        ],
                    ]
                ]
            );
        } catch (Exception $e) {
            $status = $e->getCode();
            if ($status === 0) {
                $status = 500;
            }

            Log::error(
                'Error request',
                [
                    'exception' => $e,
                    'Error ' => $e->getMessage(),
                    'code' => $e->getCode()
                ]
            );
            return new Response(
                $status,
                ['error'],
                json_encode(
                    [
                        'data' => [
                            'error' => $status,
                            'message' => $e->getMessage()
                        ]
                    ],
                ),
            );
        }
    }

    /**
     * @param $references
     * @param string $emailName
     * @param string $token
     * @return void
     * @throws GuzzleException
     */
    public function create($references, string $emailName, string $token)
    {
        foreach ($references as $data) {
            $response = $this->request($data, $emailName, $token);
            $this->responseUpdate($response);
        }
    }
}
