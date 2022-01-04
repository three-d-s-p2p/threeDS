<?php

namespace Larangogon\ThreeDS\Traits;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
     * @throws Exception|GuzzleException
     */
    protected function authorization($data, string $emailName, string $token)
    {
        try {
            $initial = microtime(true);
            $this->chunkInputData($data, $emailName, $token);

            Log::info(
                'Completed process',
                [
                    'Final time' => microtime(true) - $initial,
                    'Memory' => (memory_get_usage() / 1024) / 1024 . ' MB',
                ]
            );
            while (pcntl_waitpid(0, $status) != -1);
        } catch (Exception $e) {
            Log::error(
                'Error authorization',
                [ 'Error ' => $e ]
            );
            $this->emailError($e, $emailName);
        }
    }

    /**
     * @param $references
     * @param string $emailName
     * @param string $token
     * @return void
     * @throws Exception|GuzzleException
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
            $this->emailError($e, $emailName);
        }
    }

    /**
     * @param $error
     * @param string $emailName
     * @return mixed
     * @throws Exception
     */
    public function emailError($error, string $emailName)
    {
        $email = new ErrorMail($emailName, $error);
        Mail::to($emailName)->send($email);
        throw new Exception('email General Error Process');
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
            $this->response($response, $data, count($references));
        }
    }

    /**
     * @param $data
     * @param string $emailName
     * @param string $token
     * @return ResponseInterface|void
     * @throws GuzzleException
     */
    public function request($data, string $emailName, string $token)
    {
        try {
            return $this->getClient()->post(
                'https://3dss-test.placetopay.com/api/v1/merchants',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => $token
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
                            [
                                'admin@admin.com' => $data->invitations
                            ]
                        ]
                    ]
                ]
            );
        } catch (Exception $e) {
            $this->emailError($e, $emailName);
        }
    }

    /**
     * @param $response
     * @param $data
     * @param int $size
     * @return void
     */
    public function response($response, $data, int $size)
    {
        $status = $response->getStatusCode();
        $res = $response->getBody()->getContents();
        $response = json_decode($res);

        switch ($status) {
            case 200:
                $dataToken = [
                    'token' => $response,
                    'message' => null,
                    'code' => null,
                    'error' => null
                ];
                $this->arrayInsert($dataToken, $size);
                break;
            case '401':
                $dataToken = [
                    'token' => null,
                    'message' => $response->status->message,
                    'code' => $response->status->code,
                    'error' => 'No autenticado'
                ];
                $this->arrayInsert($dataToken, $size);
                break;
            case '422':
                $dataToken = [
                    'token' => null,
                    'message' => $response->status->message,
                    'code' => $response->status->code,
                    'error' => 'Mensajes de validación de datos'
                ];
                $this->arrayInsert($dataToken, $size);
                break;
            default:
                return $response->getStatusCode();
        }
    }

    /**
     * @return Client
     */
    private function getClient(): Client
    {
        return new Client();
    }

    /**
     * @param $data
     * @param int $size
     * @return void
     */
    public function arrayInsert($data, int $size)
    {
        try {
            array_push($this->datas, $data);
            if (count($this->datas) === $size) {
                Token::insert($this->datas);
                $this->datas = [];
            }
        } catch (Exception $e) {
            Log::error(
                'Error arrayInsert',
                [ 'Error ' => $e->getMessage() ]
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
    public function update($references, string $emailName, string $token)
    {
        foreach ($references as $data) {
            $response = $this->request($data, $emailName, $token);
            $this->responseUpdate($response);
        }
    }

    /**
     * @param $response
     * @return array|mixed
     */
    public function responseUpdate($response)
    {
        $status = $response->getStatusCode();

        switch ($status) {
            case 200:
                return $response;
                break;
            case '401':
                return [
                    'token' => null,
                    'message' => $response->status->message,
                    'code' => $response->getStatusCode(),
                    'error' => 'No autenticado'
                ];
                break;
            case '422':
                return [
                    'token' => null,
                    'message' => $response->status->message,
                    'code' => $response->getStatusCode(),
                    'error' => 'Mensajes de validación de datos'
                ];
                break;
            case '404':
                return [
                    'token' => null,
                    'message' => $response->status->message,
                    'code' => $response->getStatusCode(),
                    'error' => 'El comercio no existe'
                ];
                break;
            default:
                return $response->getStatusCode();
        }
    }
}
