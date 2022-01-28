<?php

namespace Larangogon\ThreeDS\Tests;

class ComponentTest
{
    public function collectTest(): \Illuminate\Support\Collection
    {
        return collect(
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
                    'invitations' => 'admin@admin.com'
                ],
                (object)[
                    'id' => 2,
                    'name' => 'EGM Ingenieria sin frondteras test ',
                    'brand' => 'placetopay3',
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
    }

    public function objectTest(): array
    {
        return [
            'id' => 1,
            'name' => 'EGM Ingenieria sin frondteras test three',
            'brand' => 'placetopay1',
            'country' => 'COL',
            'currency' => 'COP',
            'type' => 'RUT',
            'number' => '123456789-0',
            'url' => 'https://www.placetopay2.com',
            'mcc' => 742,
            'isicClass' => 111,
            'nameBranch' => 'Oficina principal',
            'franchise' => 1,
            'acquirerBIN' => 12345678910,
            'version' => 2,
            'invitations' => 'leidy.arango@evertecinc.com'
        ];
    }

    public function collectUpdateTest(): \Illuminate\Support\Collection
    {
        return collect(
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
                    'url' => 'https://www.placetopay3.com',
                    'nameBranch' => 'Oficina principal3',
                    'merchantID' => 1
                ]
            ]
        );
    }

    public function objectUpdateOrCreateTest(): array
    {
        return [
            'id' => 1,
            'name' => 'EGM Ingenieria sin frondteras test three',
            'brand' => 'placetopay1',
            'country' => 'COL',
            'currency' => 'COP',
            'type' => 'RUT',
            'number' => '123456789-0',
            'url' => 'https://www.placetopay2.com',
            'mcc' => 742,
            'isicClass' => 111,
            'nameBranch' => 'Oficina principal',
            'franchise' => 1,
            'acquirerBIN' => 12345678910,
            'version' => 2,
            'invitations' => 'leidy.arango@evertecinc.com',
            'merchantID' => 1
        ];
    }
}
