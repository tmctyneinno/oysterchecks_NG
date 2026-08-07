<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResendAddressWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 30;
    public $tries = 3;

    public function __construct(public $customerRef, public $token)
    {
    }

    public function handle()
    {
        $client = new Client();

        $client->request('post', "https://api.qoreid.com/v1/webhooks/collection/address?customerReference={$this->customerRef}", [
            'headers' => [
                'accept' => 'application/json',
                'authorization' => "Bearer {$this->token}",
                'content-type' => 'application/json',
            ]
        ]);
    }
}
