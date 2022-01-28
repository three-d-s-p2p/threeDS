<?php

namespace Larangogon\ThreeDS\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Mail;
use Larangogon\ThreeDS\Contracts\ClientContract;
use Larangogon\ThreeDS\Mail\ErrorMail;
use Larangogon\ThreeDS\processThreeDS;
use Larangogon\ThreeDS\Traits\ProcessableTrait;

class ProcessThreeDSTest extends TestCase
{
    use ProcessableTrait;

    protected string $emailName = 'larangogon@uniminuto.edu.com';

    protected string $token = '';


    /**
     * @test
     */
    public function processThreeDS()
    {
        $mock = new MockHandler(
            [
            new Response(
                200,
                ['X-Foo' => 'Bar'],
                json_encode(
                    [
                    'status' => [
                    'code' => 1000
                    ],
                    'data' => [
                        'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...',
                        'id' => 3
                    ]
                    ]
                ),
            ),
            new RequestException('Error Communicating with Server', new Request('POST', 'test'))
            ]
        );
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $this->app->bind(ClientContract::class, fn() => $client);

        $collect = new componentTest();
        $data = $collect->collectTest();

        $threeDS = new processThreeDS();
        $threeDS->createRequest($data, $this->emailName, $this->token);

        $this->assertTrue(true);
    }

    /**
     * @test
     * @throws GuzzleException
     */
    public function requestThreeDS()
    {
        $mock = new MockHandler(
            [
                new Response(
                    200,
                    ['X-Foo' => 'Bar'],
                    json_encode(
                        [
                            'status' => [
                                'code' => 1000
                            ],
                            'data' => [
                                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...',
                                'id' => 3
                            ]
                        ]
                    ),
                ),
                new RequestException('Error Communicating with Server', new Request('POST', 'test'))
            ]
        );
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $this->app->bind(ClientContract::class, fn() => $client);

        $collect = new componentTest();
        $data = $collect->objectTest();

        $threeDS = new processThreeDS();
        $threeDS->request((object)$data, $this->emailName, $this->token);

        $this->assertTrue(true);
    }

    /**
     * @test
     * @return void
     */
    public function processThreeDSUpdate()
    {
        $mock = new MockHandler(
            [
                new Response(
                    200,
                    ['X-Foo' => 'Bar'],
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

        $collect = new componentTest();
        $data = $collect->collectUpdateTest();

        $threeDS = new processThreeDS();
        $threeDS->update($data, $this->emailName, $this->token);
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function processPaymentMailTest()
    {
        $error = ['The field is required.'];
        Mail::fake();

        $email = new ErrorMail($this->emailName, $error);
        Mail::to($this->emailName)->send($email);

        Mail::assertSent(ErrorMail::class);

        $this->assertTrue(true);
    }
}
