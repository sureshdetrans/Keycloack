<?php

namespace App\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class RegisterationController extends AbstractController
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
     * @Route("/registration", name="registration")
     */
    public function registerNewUser(Request $request): ?Response
    {
        $requestObj = json_decode($request->getContent());
        $authorization = $request->headers->get('Authorization');

        var_dump($requestObj->enabled);
        var_dump($authorization);
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/RegisterationController.php',
        ]);
    }
}
