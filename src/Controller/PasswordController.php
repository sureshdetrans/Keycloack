<?php

namespace App\Controller;

use App\Enum\KeyCloakConfig;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PasswordController extends AbstractController
{

    /**
     * @var ClientInterface
     */
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client(['timeout' => 3]);
    }

    public function updatePassword($url, $passwordToUpdate, $authorization): ?Response
    {

        try {
            $data = json_encode(['type' => "password", 'value' => $passwordToUpdate, 'temporary' => false]);
            $response = $this->httpClient->request(
                "PUT",
                $url . KeyCloakConfig::Key_Cloak_Set_Password,
                [
                    'headers' => [
                        'Authorization' => $authorization,
                        'Content-Type' => "application/json",
                    ],
                    'body' => $data
                ]
            );

            if ($response && $response->getStatusCode() === 204) {
                $data = json_decode($response->getBody(), true);
                return $this->json($data, Response::HTTP_NO_CONTENT, array(
                    'Content-Type' => 'application/json',
                ));
            }

        } catch (GuzzleException $e) {
            return $this->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, array(
                'Content-Type' => 'application/json',
            ));
        }
    }

}
