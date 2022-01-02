<?php

namespace Larangogon\ThreeDS\Tests;

use Dotenv\Dotenv;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Mail;
use Larangogon\ThreeDS\Concrete\ThreeDSConcrete;
use Larangogon\ThreeDS\Mail\ErrorMail;
use Larangogon\ThreeDS\processThreeDS;
use Larangogon\ThreeDS\Traits\ProcessableTrait;
use PHPUnit\Framework\TestCase;

class ProcessTest extends TestCase
{
    use ProcessableTrait;


    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->data = collect(
            [
            [
                'name' => 'EGM Ingenieria sin frondteras',
                'brand' => 'placetopay',
                'country' => 'COL',
                'currency' => 'COP',
                'type' => 'RUT',
                'number' => '123456789-0',
                'url' => 'https://www.placetopay.com',
                'mcc' => 742,
                'isicClass' => 111,
                'nameBranch' => 'Oficina principal',
                'franchise' => 1,
                'acquirerBIN' => 12345678910,
                'version' => 2,
                'invitations' => null
            ],
            [
                'name' => 'EGM Ingenieria sin frondteras',
                'brand' => 'placetopay',
                'country' => 'COL',
                'currency' => 'COP',
                'type' => 'RUT',
                'number' => '123456789-0',
                'url' => 'https://www.placetopay.com',
                'mcc' => 742,
                'isicClass' => 111,
                'nameBranch' => 'Oficina principal',
                'franchise' => 1,
                'acquirerBIN' => 12345678910,
                'version' => 2,
                'invitations' => null
            ]
            ]
        );

        $this->emailName = env('EMAIL', 'johannitaarango2@gmail.com');
        $this->token = env('TOKEN', '234567dfghjfgh567');
    }


    /**
     * @test
     */
    public function createRequest()
    {
        $threeDS = new ThreeDSConcrete();
        $threeDS->process($this->data, $this->emailName, $this->token);
    }

    /**
     * @test
     */
    public function processThreeDS()
    {
        $threeDS = new processThreeDS();
        $threeDS->createRequest($this->data, $this->emailName, $this->token);
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
    }

    /**
     * @test
     * @throws GuzzleException
     */
    public function status()
    {
        $response = $this->request($this->data, $this->emailName, $this->token);

        $response->assertStatus(200);
    }
}
