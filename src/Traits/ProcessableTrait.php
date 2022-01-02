<?php

namespace Larangogon\ThreeDS\Traits;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Larangogon\ThreeDS\Mail\ErrorMail;
use Larangogon\ThreeDS\Models\Token;

trait ProcessableTrait
{
    protected array $pids = [];
    protected array $datas = [];

    /**
     * @param $data
     * @param string $emailName
     * @param string $token
     * @return void
     * @throws Exception
     */
    protected function authorization($data, string $emailName, string $token)
    {
        try {
            $initial = microtime(true);
            $perPage = $data->count();
            do {
                $references = $data->toBase()->cursor();
                $this->chunkInputData($references, $emailName, $token);
                $size = $references->count();
            } while ($size = !$perPage);

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
                [
                    'Error ' => $e->getMessage(),
                    ]
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
                    if (count($this->pids) >= 20) {
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
                [
                    'Error ' => $e->getMessage(),
                    ]
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
     * @return mixed|void
     * @throws GuzzleException
     */
    public function request($data, string $emailName, string $token)
    {
        //data type objet
        try {
            $response = $this->getClient()->post(
                'https://3dss-test.placetopay.com/api/v1/merchants',
                [
                'json' => [
                    'Accept' => 'string',
                    'Authorization' => $token,
                    'name' => 'EGM Ingenieria sin frondteras',
                    'brand' => 'placetopay',
                    'country' => 'COL',
                    'currency' => 'COP',
                    'document' => [
                        'type' => 'RUT',
                        'number' => '123456789-0'
                    ],
                    'url' => 'https://www.placetopay.com',
                    'mcc' => 742,
                    'isicClass' => 111,

                    'branch' => [
                        'name' => 'Oficina principal',
                        'brand' => 'placetopay uno',
                        'country' => 'COL',
                        'currency' => 'COP'
                    ],
                    'subscriptions' => [
                        [
                            'franchise' => 1,
                            'acquirerBIN' => 12345678910,
                            'version' => 2
                        ]
                    ],
                    'invitations' => [
                        [
                            'admin@admin.com' => null
                        ]
                    ]
                ]
                ]
            );

            return json_decode($response->getBody()->getContents());
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
        $status = $response->status->status;
        //validar que retorna

        switch ($status) {
            case 'OK': //200
                $dataToken = [
                    'token' => $response->data,
                    'message' => null,
                    'code' => null,
                    'error' => null
                ];
                $this->arrayInsert($dataToken, $size);
                break;
            case 'ERROR': // cuando retorna error
                $dataToken = [
                    'token' => null,
                    'message' => $response->status->message,
                    'code' => $response->status->code,
                    'error' => null
                ];
                $this->arrayInsert($dataToken, $size);
                break;
            case 'FAILED':
                $dataToken = [
                    'token' => null,
                    'message' => $response->message,
                    'code' => null,
                    'error' => null
                ];
                $this->arrayInsert($dataToken, $size);
                break;
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
        array_push($this->datas, $data);
        if (count($this->datas) === $size) {
            Token::insert($this->datas);
            $this->datas = [];
        }
    }
}
