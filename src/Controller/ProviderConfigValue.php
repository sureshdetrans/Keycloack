<?php

namespace App\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProviderConfigValue extends AbstractController
{

    /**
     * @var ClientInterface
     */
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client(['timeout' => 3]);
    }

    private $key_Cloak_Base_Url = 'http://localhost:8080';
    private $key_Cloak_AuthToken_Url = "/auth/realms/master/protocol/openid-connect/token";
    private $key_Cloak_Grant_Type = 'password';
    private $key_Cloak_Client_Id = 'rest-client';
    private $key_Cloak_Client_Secret = '8cc68536-52a8-4cc1-a3fc-8448bed05b2d';
    private $key_Cloak_Register_User = "/auth/admin/realms/master/users";
    private $key_Cloak_Set_Password = "/reset-password";

    function getClientId()
    {
        return $this->key_Cloak_Client_Id;
    }

    function getKeyCloakBaseUrl()
    {
        return $this->key_Cloak_Base_Url;
    }

    function getKeyCloakAuthTokenURL()
    {
        return $this->key_Cloak_AuthToken_Url;
    }

    function getKeyCloakGrantType()
    {
        return $this->key_Cloak_Grant_Type;
    }

    function getKeyCloakClientSecret()
    {
        return $this->key_Cloak_Client_Secret;
    }

    function getKeyCloakRegisterUser()
    {
        return $this->key_Cloak_Register_User;
    }

    function getKeyCloakSetPassword()
    {
        return $this->key_Cloak_Set_Password;
    }

}
