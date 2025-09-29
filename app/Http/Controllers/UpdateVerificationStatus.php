<?php

namespace App\Http\Controllers;

use App\Models\AddressVerificationDetail;
use App\Services\Base;
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
          $token = $this->base->generateToken()['accessToken'];
             AddressVerificationDetail::where('status', 'IN_PROGRESS')
             ->chunk(100, function($ddresses) use ($token){
            foreach($ddresses as $address){
            $param =[
                'verificationType' => 'address',
                'customerReference' =>$address->reference_id
            ];
            $resp =   $this->base->baseUrl('post', "https://api.qoreid.com/v1/webhooks/collection/address/",$param,$token);
             }
             });

    }
}
