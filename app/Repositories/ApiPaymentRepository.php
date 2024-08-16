<?php

namespace App\Repositories;


use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ApiPaymentRepository implements ApiPaymentRepositoryInterface
{

    private $headers;

    private $token;

    private $baseUri;

    private $url;

    private $username;

    private $secret;


    public function init(): void
    {
        $response = Http::withHeaders( [
                        'Accept'       => 'application/json',
                        'Content-Type' => 'application/json',
                    ])->post( $this->getUrl() . '/api/v1/login', 
                    [
                        "email"    => $this->getUsername(),
                        "password" => $this->getSecret()
                        ]);
        if ( $response->successful() ) {
            $this->token = $response->json()['access_token'];
        }
       
    }

    public function execute($method, $data): Array
    {

        $response = Http::withHeaders( [
               'Accept' => 'application/json',
               'Content-Type' => 'application/json',
               'Authorization' => 'Bearer ' . $this->token
            ])->post( $this->getUrl() . "/api/v1/payment/" . $method,
                 $data);
    
        return $response->json();
       
    }

    public function getHeaders(): string
    {
        return $this->headers;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    public function getUrl(): string
    {
        return config("phone.phone_api_credentials.payment_url");
    }

    public function getUsername(): string
    {
        return config("phone.phone_api_credentials.payment_id");
    }

    public function getSecret(): string
    {
        return  config("phone.phone_api_credentials.payment_key");
    }


}
