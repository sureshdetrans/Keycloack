<?php

namespace App\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthenticationController extends AbstractController
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client(['timeout' => 3]);
    }

    /**
     * @Route("/authentication", name="authentication")
     */
    public function index(): ?Response
    {
        try {
            $response = $this->httpClient->request(
                "POST",
                "http://localhost:8080/auth/realms/master/protocol/openid-connect/token",
                [
                    'headers' => [
                        'Content-Type' => "application/x-www-form-urlencoded",
                    ],

                    'form_params' => ["grant_type" => "password",
                        "client_id" => "rest-client",
                        "client_secret" => "8cc68536-52a8-4cc1-a3fc-8448bed05b2d",
                        "username" => "hexa",
                        "password" => "hexa"]
                ]
            );

            if ($response && $response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                return $this->json($data, Response::HTTP_CREATED, array(
                    'Content-Type' => 'application/json',
                ));
            }
            return null;
        } catch (GuzzleException $e) {
            dump($e->getMessage());
            return null;
        }
    }
}
