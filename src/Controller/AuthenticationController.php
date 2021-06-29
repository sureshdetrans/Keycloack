<?php

namespace App\Controller;

use App\Enum\KeyCloakConfig;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * @param Request $request
     * @Route("/authToken", name="authToken")
     */
    public function getAuthToken(Request $request): Response
    {
        $requestObj = json_decode($request->getContent());
        if ($requestObj !== null) {
            $userName = $requestObj->username;
            $password = $requestObj->password;
            if (isset($userName) && isset($password)) {
                try {
                    $response = $this->httpClient->request(
                        "POST",
                        KeyCloakConfig::Key_Cloak_Base_Url . KeyCloakConfig::Key_Cloak_AuthToken_Url,
                        [
                            'headers' => [
                                'Content-Type' => "application/x-www-form-urlencoded",
                            ],
                            'form_params' => [
                                "grant_type" => KeyCloakConfig::Key_Cloak_Grant_Type,
                                "client_id" => KeyCloakConfig::Key_Cloak_Rest_Client,
                                "client_secret" => KeyCloakConfig::Key_Cloak_Client_Secret,
                                "username" => $userName,
                                "password" => $password]
                        ]
                    );

                    if ($response && $response->getStatusCode() === 200) {
                        $data = json_decode($response->getBody(), true);
                        return $this->json($data, Response::HTTP_CREATED, array(
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
        return $this->json("Error : username and password are required.", Response::HTTP_BAD_REQUEST);
    }

}
