<?php


namespace App\Controller;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class KeycloakConnectClientAdapter
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string arbitrary id value
     */
    private $clientID;

    /**
     * @var string arbitrary name value
     */
    private $clientName;

    /**
     * @var string arbitrary secret value
     */
    private $clientSecret;

    /**
     * @var array holds the provider configuration
     */
    private $providerConfig = array();

    /**
     * @var string if we acquire an access token it will be stored here
     */
    protected $accessToken;

    /**
     * @var string if we acquire a refresh token it will be stored here
     */
    private $refreshToken;

    /**
     * @var string if we acquire an id token it will be stored here
     */
    protected $idToken;

    /**
     * @var string stores the token response
     */
    private $tokenResponse;


    protected $enc_type = PHP_QUERY_RFC1738;

    /**
     * @var int timeout (seconds)
     */
    protected $timeOut = 60;

    /**
     * @var bool Verify SSL peer on transactions
     */
    private $verifyPeer = false;

    /**
     * @var bool Verify peer hostname on transactions
     */
    private $verifyHost = false;


    /**
     * @var string stores the token response
     */
    private $providerConfigValue;

    public function __construct(LoggerInterface $logger, ProviderConfigValue $providerConfigValue)
    {
        $this->logger = $logger;
        $this->providerConfigValue = $providerConfigValue;
        $this->clientID = $this->providerConfigValue->getClientId();
        $this->clientSecret = $this->providerConfigValue->getKeyCloakClientSecret();
    }

    /**
     * @param string $userName
     * Q@param string $password
     * @return Response
     */
    public function authenticate($userName, $password)
    {
        // add data validation here later before calling private method
        return $this->requestAuthorization($userName, $password);
    }


    /**
     * Authorization data
     * @return Response
     */
    private function requestAuthorization($username, $password)
    {
        $authEndPoint = $this->providerConfigValue->getKeyCloakBaseUrl() . $this->providerConfigValue->getKeyCloakAuthTokenURL();
        $headers = [];

        $post_data = array(
            "username" => $username,
            "password" => $password,
            "grant_type" => $this->providerConfigValue->getKeyCloakGrantType(),
            "client_id" => $this->clientID,
            "client_secret" => $this->clientSecret
        );

        // Convert token params to string format
        $post_params = http_build_query($post_data, null, '&', $this->enc_type);
        return json_decode($this->fetchResponse($authEndPoint, $post_params, $headers));
    }


    /**
     * Dynamic registration
     */
    public function register($authorization, $data)
    {
        $registrationEndpoint = $this->providerConfigValue->getKeyCloakBaseUrl() . $this->providerConfigValue->getKeyCloakRegisterUser();
        // Accept json to indicate response type
        $headers = ["Authorization: " . $authorization];
        //  Convert token params to string format
        return json_decode($this->fetchResponse($registrationEndpoint, $data, $headers));
    }

    /**
     * @return object
     */
    public function getAccessTokenHeader()
    {
        return $this->decodeJWT($this->accessToken);
    }

    /**
     * @return object
     */
    public function getAccessTokenPayload()
    {
        return $this->decodeJWT($this->accessToken, 1);
    }

    /**
     * @param string $jwt encoded JWT
     * @param int $section the section we would like to decode
     * @return object
     */
    protected function decodeJWT($jwt, $section = 0)
    {
        $parts = explode('.', $jwt);
        return json_decode($this->base64url_decode($parts[$section]));
    }

    /**
     * A wrapper around base64_decode which decodes Base64URL-encoded data,
     * which is not the same alphabet as base64.
     * @param string $base64url
     * @return bool|string
     */
    private function base64url_decode($base64url)
    {
        return base64_decode($this->b64url2b64($base64url));
    }

    /**
     * Per RFC4648, "base64 encoding with URL-safe and filename-safe
     * alphabet".  This just replaces characters 62 and 63.  None of the
     * reference implementations seem to restore the padding if necessary,
     * but we'll do it anyway.
     * @param string $base64url
     * @return string
     */
    private function b64url2b64($base64url)
    {
        $padding = strlen($base64url) % 4;
        if ($padding > 0) {
            $base64url .= str_repeat('=', 4 - $padding);
        }
        return strtr($base64url, '-_', '+/');
    }

    /**
     * @param string $url
     * @param string | null $post_body string If this is set the post type will be POST
     * @param array $headers Extra headers to be send with the request. Format as 'NameHeader: ValueHeader'
     * @return mixed
     */
    protected function fetchResponse($url, $post_body = null, $headers = array())
    {
        $ch = curl_init();

        // Determine whether this is a GET or POST
        if ($post_body !== null) {
            // curl_setopt($ch, CURLOPT_POST, 1);
            // Alows to keep the POST method even after redirect
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);

            // Default content type is form encoded
            $content_type = 'application/x-www-form-urlencoded';

            // Determine if this is a JSON payload and add the appropriate content type
            if (is_object(json_decode($post_body))) {
                $content_type = 'application/json';
            }

            // Add POST-specific headers
            $headers[] = "Content-Type: {$content_type}";
        }

        // If we set some headers include them
        if (count($headers) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // Set URL to download
        curl_setopt($ch, CURLOPT_URL, $url);

        if (isset($this->httpProxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $this->httpProxy);
        }

        // Include header in result? (0 = yes, 1 = no)
        curl_setopt($ch, CURLOPT_HEADER, 0);

        // Allows to follow redirect
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_ENCODING, "");

        /**
         * Set cert
         * Otherwise ignore SSL peer verification
         */
        if (isset($this->certPath)) {
            curl_setopt($ch, CURLOPT_CAINFO, $this->certPath);
        }

        if ($this->verifyHost) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        if ($this->verifyPeer) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        // Should cURL return or print out the data? (true = return, false = print)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeOut);

        // Download the given URL, and return output
        $output = curl_exec($ch);

        // HTTP Response code from server may be required from subclass
        $info = curl_getinfo($ch);

        if ($output === false) {
            throw new ClientException('Curl error: (' . curl_errno($ch) . ') ' . curl_error($ch));
        }

        // Close the cURL resource, and free system resources
        curl_close($ch);

        return $output;
    }

}
