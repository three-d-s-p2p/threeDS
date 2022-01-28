<?php

namespace Larangogon\ThreeDS\Traits;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Larangogon\ThreeDS\Contracts\ClientContract;
use Psr\Http\Message\ResponseInterface;

trait ConnectionTrait
{
    /**
     * @param object $data
     * @param string $token
     * @return Response|ResponseInterface
     * @throws GuzzleException
     */
    public function requestConnectionCreate(object $data, string $token)
    {
        try {
            return $this->getClient()->post(
                'https://3dss-test.placetopay.com/api/v1/merchants',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer {$token}"
                    ],
                    'json' => [
                        'name' => $data->name,
                        'brand' => $data->brand,
                        'country' => $data->country,
                        'currency' => $data->currency,
                        'document' => [
                            'type' => $data->type,
                            'number' => $data->number
                        ],
                        'url' => $data->url,
                        'mcc' => $data->mcc,
                        'isicClass' => $data->isicClass,
                        'branch' => [
                            'name' => $data->nameBranch,
                            'brand' => $data->brand,
                            'country' => $data->country,
                            'currency' => $data->currency,
                        ],
                        'subscriptions' => [
                            [
                                'franchise' => $data->franchise,
                                'acquirerBIN' => $data->acquirerBIN,
                                'version' => $data->version
                            ]
                        ],
                        'invitations' => [
                            $data->invitations
                        ]
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
     * @return Client
     */
    private function getClient(): Client
    {
        return app(ClientContract::class);
    }

    /**
     * @param object $data
     * @param string $token
     * @return Response|ResponseInterface
     * @throws GuzzleException
     */
    public function requestConnectionUpdate(object $data, string $token)
    {
        try {
            return $this->getClient()->post(
                "https://3dss-test.placetopay.com/api/v1/{$data->merchantId}/branches",
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
}
