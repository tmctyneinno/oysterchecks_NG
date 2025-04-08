<?php 

namespace App\Services;
use GuzzleHttp\Client;
class Base 
{


    public function baseUrl($method, $url, $body, $token)
    {

        $client = new Client();
      $req =  $client->request($method, $url, [
            'headers' => [
                'accept' => 'application/json',
                'authorization' => "Bearer $token",
                'content-type' => 'application/json',
            ],
            'body' => json_encode($body)
        ]);

        return $req->getBody();
    }



    public function generateToken()
    {
        $url = 'https://api.qoreid.com/token';
        $secret = config('app.verifyMeSecret');
        $clientId = config('app.verifyMeClientId');

        // dd($clientId, $secret);
        $body = [
            'clientId' =>  $clientId,
            'secret' =>  $secret
        ];

        $client = new Client();
        $req = $client->request('POST', $url, [
            'body' => json_encode($body),
            'headers'=>[
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ]
        ]);
    dd($req);
        if($req)
        {
           return json_decode($req->getBody(), true);

        }
    }
}