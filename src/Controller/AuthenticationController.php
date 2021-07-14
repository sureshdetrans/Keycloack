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

    /**
     * @var KeycloakConnectClientAdapter
     */
    private $keycloakConnectClientAdapter;

    public function __construct(KeycloakConnectClientAdapter $keycloakConnectClientAdapter)
    {
        $this->httpClient = new Client(['timeout' => 3]);
        $this->keycloakConnectClientAdapter = $keycloakConnectClientAdapter;
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
            $data = $this->keycloakConnectClientAdapter->authenticate($userName, $password);
            return $this->json($data, Response::HTTP_CREATED, array(
                'Content-Type' => 'application/json',
            ));
        }

        return $this->json("Error : username and password are required.", Response::HTTP_BAD_REQUEST);
    }

}
