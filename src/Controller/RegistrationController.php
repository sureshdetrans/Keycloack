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

class RegistrationController extends AbstractController
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var PasswordController
     */
    private $password;

    /**
     * @var KeycloakConnectClientAdapter
     */
    private $keycloakConnectClientAdapter;

    public function __construct(PasswordController $password, KeycloakConnectClientAdapter $keycloakConnectClientAdapter)
    {
        $this->httpClient = new Client(['timeout' => 3]);
        $this->password = $password;
        $this->keycloakConnectClientAdapter = $keycloakConnectClientAdapter;
    }

    /**
     * @Route("/registration", name="registration")
     */
    public function registerNewUser(Request $request): Response
    {
        $requestObj = json_decode($request->getContent());
        $authorization = $request->headers->get('Authorization');

        if ($authorization !== null && $requestObj !== null) {
            try {
                $data = json_encode(['enabled' => true, 'username' => $requestObj->username, 'emailVerified' => $requestObj->emailVerified,
                    'email' => $requestObj->email, 'firstName' => $requestObj->firstName, 'lastName' => $requestObj->lastName, "attributes" => $requestObj->attributes]);

                $data = $this->keycloakConnectClientAdapter->register($authorization, $data);

                return $this->json($data, Response::HTTP_CREATED, array(
                    'Content-Type' => 'application/json',
                ));

            } catch (GuzzleException $e) {
                return $this->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, array(
                    'Content-Type' => 'application/json',
                ));
            }
        }

        return $this->json("Error ", Response::HTTP_BAD_REQUEST);
    }
}
