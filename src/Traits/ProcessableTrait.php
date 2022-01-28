<?php

namespace Larangogon\ThreeDS\Traits;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Larangogon\ThreeDS\Contracts\ClientContract;
use Larangogon\ThreeDS\Mail\ErrorMail;
use Larangogon\ThreeDS\Models\Token;
use Psr\Http\Message\ResponseInterface;

trait ProcessableTrait
{
    protected array $pids = [];
    protected array $datas = [];

    /**
     * @param $data
     * @param string $emailName
     * @param string $token
     * @return void
     * @throws GuzzleException
     */
    protected function authorization($data, string $emailName, string $token)
    {
        try {
            $initial = microtime(true);
            $this->chunkInputData($data, $emailName, $token);

            while (pcntl_waitpid(0, $status) != -1);

            Log::info(
                'Completed process',
                [
                    'Final time' => microtime(true) - $initial,
                    'Memory' => (memory_get_usage() / 1024) / 1024 . ' MB',
                ]
            );
        } catch (Exception $e) {
            Log::error(
                'Error authorization',
                [ 'Error ' => $e->getMessage() ]
            );
            $this->emailError($e, $emailName);
        }
    }

    /**
     * @param $references
     * @param string $emailName
     * @param string $token
     * @return void
     * @throws GuzzleException
     */
    protected function chunkInputData($references, string $emailName, string $token)
    {
        try {
            $references->chunk(500)->each(
                function ($chunk) use ($token, $emailName) {
                    if (count($this->pids) >= 10) {
                        $pid = pcntl_waitpid(-1, $status);
                        unset($this->pids[$pid]);
                    }

                    $pid = pcntl_fork();

                    if ($pid == -1 || $pid === null) {
                        exit("Error forking...\n");
                    } elseif ($pid) {
                        $this->pids[] = $pid;
                    } else {
                        $this->create($chunk, $emailName, $token);
                        exit();
                    }
                }
            );

            foreach ($this->pids as $pid) {
                pcntl_waitpid($pid, $status);
                unset($this->pids[$pid]);
            }
        } catch (Exception $e) {
            Log::error(
                'Error chunkInputData',
                [ 'Error ' => $e->getMessage() ]
            );
        }
    }

    /**
     * @param $error
     * @param string $emailName
     * @return void
     */
    public function emailError($error, string $emailName)
    {
        $email = new ErrorMail($emailName, $error);
        Mail::to($emailName)->send($email);
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
            $this->response($response, count($references));
        }
    }

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
            return new Response(
                400,
                [],
                json_encode(
                    [
                        'data' => [
                            [
                                'error' => $e
                            ]
                        ]
                    ],
                ),
            );
        }
    }

    /**
     * @param $response
     * @param int $size
     * @return void
     */
    public function response($response, int $size)
    {
        $status = $response->getStatusCode();
        $response = json_decode($response->getBody()->getContents());

        switch ($status) {
            case 200:
                $dataToken = [
                'token' => $response->data->token,
                'message' => $response,
                'idSubscriptions' => $response->data->id,
                'code' => $status,
                'error' => null
                ];
                break;
            case 422:
            case 401:
                $dataToken = [
                'token' => null,
                'message' => null,
                'idSubscriptions' => null,
                'code' => $status,
                'error' => $response->data->error
                ];
                break;
            default:
                $dataToken = [
                'token' => null,
                'message' => $response,
                'idSubscriptions' => null,
                'code' =>  $status,
                'error' => 'not mapped error'
                ];
        }
            $this->arrayInsert($dataToken, $size);
    }

    /**
     * @return Client
     */
    private function getClient(): Client
    {
        return app(ClientContract::class);
    }

    /**
     * @param $data
     * @param int $size
     * @return void
     */
    public function arrayInsert($data, int $size)
    {
        $this->datas[] = $data;
        if (count($this->datas) === $size) {
            Token::insert($this->datas);
            $this->datas = [];
        }
    }


    /**
     * @param $response
     * @return array|mixed
     */
    public function responseUpdate($response)
    {
        $status = $response->getStatusCode();
        $response = json_decode($response->getBody()->getContents());

        switch ($status) {
            case 200:
                return $response;
            case 422:
            case 401:
            case 404:
                return [
                    'message' => $response,
                    'code' => $status,
                    'error' => $response->getResponse()
                ];
            default:
                return [
                    'message' => $response,
                    'code' => $status,
                    'error' => 'not mapped error',
                ];
        }
    }
}
