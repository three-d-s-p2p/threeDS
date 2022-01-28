<?php

namespace Larangogon\ThreeDS\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Larangogon\ThreeDS\Contracts\ClientContract;
use Larangogon\ThreeDS\ProcessThreeDSCreateOrUpdate;

class ProcessThreeDSCreateOrUpdateTest extends TestCase
{
    protected string $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIyIiwianRpIjoiNzMyMWY0M2U1ZTM3YTExYjlmNzc1NTYzYWViZWNkYzY2NDMxMmRiYTc4OWQxMjU1YjE2ZGRhMWUxODYwNjI1YzliMDNjYjFlMDUyMGZlZTMiLCJpYXQiOjE2NDMyMzM5MzQuNzQyNDg5LCJuYmYiOjE2NDMyMzM5MzQuNzQyNDkyLCJleHAiOjE2NDMyNjk5MzQuNzM0ODg1LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.MbmYjQEkvhI8r0Nawe5hcFaeEid27zfAM2u5oaSrOjSQxXRBLUviRyRMGnHFDZyhaDEQTpWXT1ak7p5P9FokfTDoQu2uNBQAz6CgET_LoT_Dg2_ng3cm6XscxpBr2QXiYdnlktmrxRAP4ZUCV-SsJkmLj2TVhwuFyiuWF61PHcg';

    /**
     * @test
     * @return void
     * @throws GuzzleException
     */
    public function processThreeDSUpdateOrCreate()
    {
        $mock = new MockHandler(
            [
                new Response(
                    200,
                    ['test' => 'threeds update'],
                    json_encode(
                        [
                            'status' => [
                                'code' => 1000
                            ],
                            'data' => [
                                [
                                    'id' => 1,
                                    'name' => 'Oficina Ciudad A',
                                    'brand' => 'Compañía Ciudad A',
                                    'url' => 'https://companyar-a.com'
                                ],
                                [
                                    'id' => 2,
                                    'name' => 'Oficina Ciudad B',
                                    'brand' => 'Compañía Ciudad B',
                                    'url' => 'https://company-b.com'
                                ]
                            ]
                        ],
                    ),
                ),
                new RequestException('Error Communicating with Server', new Request('POST', 'test'))
            ]
        );
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $this->app->bind(ClientContract::class, fn() => $client);

        $collect = new ComponentTest();
        $data = $collect->objectUpdateOrCreateTest();

        $threeDS = new ProcessThreeDSCreateOrUpdate();
        $threeDS->createOrUpdate((object)$data, $this->token);

        $this->assertTrue(true);
    }
}
