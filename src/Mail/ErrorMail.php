<?php

namespace Larangogon\ThreeDS\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ErrorMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    private $emailName;
    private $error;

    /**
     * @param $emailName
     * @param $error
     */
    public function __construct($emailName, $error)
    {
        $this->emailName = $emailName;
        $this->error = $error;
    }

    /**
     * @return ErrorMail
     */
    public function build(): ErrorMail
    {
        return $this->from($this->emailName)
            ->view('mails.error')
            ->with(
                [
                'date' => Carbon::now(),
                'error' => $this->error,
                ]
            );
    }
}
