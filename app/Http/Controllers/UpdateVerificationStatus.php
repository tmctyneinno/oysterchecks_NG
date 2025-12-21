<?php

namespace App\Http\Controllers;

use App\Models\AddressVerificationDetail;
use App\Services\Base;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class UpdateVerificationStatus extends Controller
{
    //

    public function __construct(
           public readonly Base $base
    )
    {
        
    }


    public function resendWebhook()
    {
        $client = new Client();
      
          $token = $this->base->generateToken()['accessToken'];
             AddressVerificationDetail::where('status', 'IN_PROGRESS')
             ->chunk(100, function($ddresses) use ($token, $client){
            foreach($ddresses as $address){
            $customerRef = $address->reference_id;
            
              $SS =   $client->request('post', "https://api.qoreid.com/v1/webhooks/collection/address?customerReference=$customerRef", [
                   'headers' => [
                   'accept' => 'application/json',
                'authorization' => "Bearer $token",
                'content-type' => 'application/json',
            ]
            ]);
            // dd(json_decode($SS->getBody(), true));
            
        }

             });


    }
}
