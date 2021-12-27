<?php

namespace Larangogon\ThreeDS\Traits;

use Exception;
use GuzzleHttp\Client;
use Larangogon\ThreeDS\Mail\ErrorMail;

trait ProcessableTrait
{
    protected $pids = [];
    protected $datas = [];

    /**
     * @param $data
     * @param string $emailName
     * @return void
     * @throws Exception
     */
    protected function authorization($data, string $emailName)
    {
        try {
            $initial = microtime(true );

            $perPage = $data->count();

            do {
                $references = $data->toBase()->cursor();

                $this->chunkInputData($references, $emailName);

                $size = $references->count();
            } while ($size = !$perPage);

            logger()->channel('stack')
                ->info('Completed process', [
                    'Final time' => microtime(true) - $initial,
                    'Memory' => (memory_get_usage() / 1024) / 1024 . ' MB',
                ]);

            while (pcntl_waitpid(0, $status) != -1);
        } catch (Exception $e) {
            logger()->channel('stack')
                ->info('Error authorization', [
                    'Error ' => $e->getMessage(),
                ]);
            $this->emailError($e, $emailName);
        }
    }

    /**
     * @param $references
     * @param string $emailName
     * @return void
     * @throws Exception
     */
    protected function chunkInputData($references, string $emailName)
    {
        try {
            $references->chunk(500)->each(function ($chunk) use ($emailName) {
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
                    $this->create($chunk, $emailName);
                    exit();
                }
            });

            foreach ($this->pids as $pid) {
                pcntl_waitpid($pid, $status);
                unset($this->pids[$pid]);
            }
        } catch (Exception $e) {
            logger()->channel('stack')
                ->info('Error chunkInputData', [
                    'Error ' => $e->getMessage(),
                ]);
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
        throw new Exception('emailImportGeneral');
    }

    /**
     * @param $references
     * @param $emailName
     * @return void
     * @throws Exception
     */
    public function create($references, $emailName)
    {
        foreach ($references as $data) {
            $response = $this->request($data, $emailName);
            $this->response($response, $data, count($references));
        }
    }

    /**
     * @param $data
     * @param string $emailName
     * @return mixed|void
     * @throws Exception
     */
    public function request($data, string $emailName)
    {
        //data type objet
        try {
            $response = $this->getClient()->post('https://3dss-test.placetopay.com/api/v1/merchants', [
                'json' => [
                    'Accept' => "string",
                    'Authorization' => "token",
                    'name' => "EGM Ingenieria sin frondteras",
                    'brand' => "placetopay",
                    'country' => "COL",
                    'currency' => "COP",
                    'document' => [
                        'type' => "RUT",
                        'number' => "123456789-0"
                    ],
                    "url" => "https://www.placetopay.com",
                    "mcc" => 742,
                    "isicClass" => 111,

                    "branch"=> [
                        "name" => "Oficina principal",
                        "brand" => "placetopay uno",
                        "country" => "COL",
                        "currency" => "COP"
                    ],
                    "subscriptions" => [
                        [
                            "franchise" => 1,
                            "acquirerBIN" => 12345678910,
                            "version" => 2
                        ]
                    ],
                    "invitations" => [
                        [
                            "admin@admin.com" => null
                        ]
                    ]
                ]
            ]);

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
            case "OK": //200
                $dataToken = [
                    "token" => $response->data,
                    "message" => null,
                    "code" => null,
                    "error" => null
                ];
                $this->arrayInsert($dataToken, $size);
                break;
            case "ERROR": // cuando retorna error
                $dataToken = [
                    "token" => null,
                    "message" => $response->status->message,
                    "code" => $response->status->code,
                    "error" => null
                ];
                $this->arrayInsert($dataToken, $size);
                break;
            case "FAILED":
                $dataToken = [
                    "token" => null,
                    "message" => $response->message,
                    "code" => null,
                    "error" => null
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


    public function sum($one, $three)
    {
        return $one + $three;
    }


    /**
     * @param array $data
     * @param int $size
     */
    public function arrayInsert(array $data, int $size)
    {
        /**
         * array_push($this->datas, $data);
        if (count($this->datas) === $size) {
        Token::insert($this->datas);
        $this->datas = [];
        }
         */
    }
}