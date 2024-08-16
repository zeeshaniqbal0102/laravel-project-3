<?php

namespace App\Repositories;

interface ApiPaymentRepositoryInterface
{
    public function init();

    public function execute($method, $data);

    public function getHeaders();
    
    public function getToken();

    public function getBaseUri();
    
    public function getUrl();
    
    public function getUsername();

    public function getSecret();

}
