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

    public function __construct(PasswordController $password)
    {
        $this->httpClient = new Client(['timeout' => 3]);
        $this->password = $password;
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
                    'email' => $requestObj->email, 'firstName' => $requestObj->firstName, 'lastName' => $requestObj->lastName]);

                $response = $this->httpClient->request(
                    "POST",
                    KeyCloakConfig::Key_Cloak_Base_Url . KeyCloakConfig::Key_Cloak_Register_User,
                    [
                        'headers' => [
                            'Authorization' => $authorization,
                            'Content-Type' => "application/json",
                        ],
                        'body' => $data
                    ]
                );

                if ($response && $response->getStatusCode() === 201) {
                    $data = json_decode($response->getBody(), true);
                    $setPasswordUrl = $response->getHeader("Location")[0];
                    $passwordUpdateResult = $this->password->updatePassword($setPasswordUrl, $requestObj->password, $authorization);

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

        return $this->json("Error ", Response::HTTP_BAD_REQUEST);

    }
}
