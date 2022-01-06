<?php

namespace Larangogon\ThreeDS\Concrete;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Larangogon\ThreeDS\Templates\ProcessTemplate;
use Larangogon\ThreeDS\Traits\ProcessableTrait;

class ThreeDSUpdateConcrete extends ProcessTemplate
{
    use ProcessableTrait;

    /**
     * @param object $data
     * @param string $emailName
     * @param string $token
     * @return false|\Psr\Http\Message\ResponseInterface|string|void
     * @throws GuzzleException
     */
    public function request(object $data, string $emailName, string $token)
    {
        try {
            return $this->getClient()->post(
                'https://3dss-test.placetopay.com/api/v1/merchants/merchantID/branches',
                [
                    'json' => [
                        'Accept' => 'string',
                        'Authorization' => $token,
                        'branches' => [
                            'name' => 'Oficina principal',
                            'brand' => 'placetopay uno',
                            'country' => 'COL',
                            'currency' => 'COP',
                            'url' => 'https://example-uno.com'
                        ],
                    ]
                ]
            );
        } catch (Exception $e) {
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
                        $this->update($chunk, $emailName, $token);
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
}
