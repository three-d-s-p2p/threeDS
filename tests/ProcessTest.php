<?php

namespace Larangogon\ThreeDS\Tests;

use Illuminate\Support\Facades\Mail;
use Larangogon\ThreeDS\Mail\ErrorMail;
use Larangogon\ThreeDS\processThreeDS;
use Larangogon\ThreeDS\Traits\ProcessableTrait;

class ProcessTest extends TestCase
{
    use ProcessableTrait;

    /**
     * @test
     */
    public function processThreeDS()
    {
        $emailName = config('config.email');
        $token = config('config.token');
        $data = collect(
            [
                (object)[
                    'id' => 1,
                    'name' => 'EGM Ingenieria sin frondteras test one',
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
                    'name' => 'EGM Ingenieria sin frondteras test ',
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
                    'invitations' => 'admin@admin.com'
                ]
            ]
        );

        $threeDS = new processThreeDS();
        $threeDS->createRequest($data, $emailName, $token);
    }

    /**
     * @test
     */
    public function requestThreeDS()
    {
        $emailName = config('config.email');
        $token = config('config.token');

        $data = [
            'id' => 1,
            'name' => 'EGM Ingenieria sin frondteras test three',
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
            'invitations' => 'leidy.arango@evertecinc.com'
        ];

        $threeDS = new processThreeDS();
        $threeDS->request((object)$data, $emailName, $token);
    }

    /**
     * @test
     * @return void
     */
    public function processThreeDSUpdate()
    {
        $emailName = config('config.email');
        $token = config('config.token');
        $data = collect(
            [
                (object)[
                    'id' => 1,
                    'brand' => 'placetopay test one',
                    'country' => 'COL',
                    'currency' => 'COP',
                    'url' => 'https://www.placetopay.com',
                    'nameBranch' => 'Oficina principal',
                    'merchantID' => 1
                ],
                (object)[
                    'id' => 2,
                    'brand' => 'placetopay test',
                    'country' => 'COL',
                    'currency' => 'COP',
                    'url' => 'https://www.placetopay.com',
                    'nameBranch' => 'Oficina principal',
                    'merchantID' => 1
                ]
            ]
        );

        $threeDS = new processThreeDS();
        $threeDS->update($data, $emailName, $token);
    }

    /**
     * @test
     */
    public function processPaymentMailTest()
    {
        $emailName = config('config.email');
        $error = ['The field is required.'];
        Mail::fake();

        $email = new ErrorMail($emailName, $error);
        Mail::to($emailName)->send($email);

        Mail::assertSent(ErrorMail::class);
    }
}
