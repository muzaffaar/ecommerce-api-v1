<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $twilio;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $this->twilio = new Client($sid, $token);
    }

    public function sendSms($to, $message)
    {
        $this->twilio->messages->create(
            $to,
            [
                'body' => $message,
                'from' => config('services.twilio.from')
            ]
        );
    }
}
