<?php

namespace Larangogon\ThreeDS\Tests;

use Illuminate\Support\Facades\Mail;
use Larangogon\ThreeDS\Mail\ErrorMail;
use Larangogon\ThreeDS\processThreeDS;
use Larangogon\ThreeDS\Traits\ProcessableTrait;
use PHPUnit\Framework\TestCase;

class ProcessTest extends TestCase
{
    use ProcessableTrait;

    /**
     * @test
     */
    public function processThreeDS()
    {
        $emailName = env('EMAIL', 'johannitaarango2@gmail.com');
        $token = env('TOKEN', '234567dfghjfgh567');

        $data = collect(
            [
                (object)[
                    'id' => 1,
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
                (object)[
                    'id' => 2,
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

        $threeDS = new processThreeDS();
        $threeDS->createRequest($data, $emailName, $token);
    }

    /**
     * @test
     */
    public function processPaymentMailTest()
    {
        $emailName = env('EMAIL', 'johannitaarango2@gmail.com');
        $error = ['The field is required.'];
        Mail::fake();

        $email = new ErrorMail($emailName, $error);
        Mail::to($emailName)->send($email);

        Mail::assertSent(ErrorMail::class);
    }

    /**
     * @test
     */
    public function requestThreeDS()
    {
        $emailName = env('EMAIL', 'johannitaarango2@gmail.com');
        $token = env('TOKEN', '234567dfghjfgh567');

        $data = [
            'id' => 1,
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
        ];

        $threeDS = new processThreeDS();
        $threeDS->request((object)$data, $emailName, $token);
    }
}
