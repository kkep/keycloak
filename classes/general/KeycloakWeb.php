<?php

// use Bitrix\Ldap\Internal\Security\Password;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Authentication\ApplicationPasswordTable;

use \Bitrix\Main\Data\Cache;


/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage keycloak
 * @author Arsen Khayrullin
 */

class KeycloakWeb
{
    /**
     * The Session key for token
     */
    const KEYCLOAK_SESSION = '_keycloak_token';

    /**
     * The Session key for state
     */
    const KEYCLOAK_SESSION_STATE = '_keycloak_state';

    /**
     * Keycloak URL
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Keycloak Realm
     *
     * @var string
     */
    protected $realm;

    /**
     * Keycloak Client ID
     *
     * @var string
     */
    protected $clientId;

    /**
     * Keycloak Client Secret
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * Keycloak OpenId Configuration
     *
     * @var array
     */
    protected $openid;

    /**
     * Keycloak OpenId Cache Configuration
     *
     * @var array
     */
    protected $cacheOpenid;

    /**
     * CallbackUrl
     *
     * @var array
     */
    protected $callbackUrl;

    /**
     * RedirectLogout
     *
     * @var array
     */
    protected $redirectLogout;

    /**
     * The state for authorization request
     *
     * @var string
     */
    protected $state;

    protected $cache;

    private $session;

    private static $instance = null;

    /**
     * The Constructor
     * You can extend this service setting protected variables before call
     * parent constructor to comunicate with Keycloak smoothly.
     *
     * @return void
     */
    public function __construct()
    {
        if (is_null($this->baseUrl)) {
            $this->baseUrl = trim(COption::GetOptionString('keycloak', 'base_url'), '/');
        }

        if (is_null($this->realm)) {
            $this->realm = COption::GetOptionString('keycloak', 'realm');
        }

        if (is_null($this->clientId)) {
            $this->clientId = COption::GetOptionString('keycloak', 'client_id');
        }

        if (is_null($this->clientSecret)) {
            $this->clientSecret = COption::GetOptionString('keycloak', 'client_secret');
        }

        if (is_null($this->cacheOpenid)) {
            $this->cacheOpenid = COption::GetOptionString('keycloak', 'cache_openid', 'N');
        }

        if (is_null($this->callbackUrl)) {
            $this->callbackUrl = COption::GetOptionString('keycloak', 'redirect_url');
        }

        if (is_null($this->redirectLogout)) {
            $this->redirectLogout = COption::GetOptionString('keycloak', 'redirect_logout', '/');
        }

        $this->cache = Cache::createInstance();

        $this->session = new Session();

        $this->state = $this->generateRandomState();
    }

    /**
     * @return static
     */
    public static function instance()
    {
        if (static::$instance === null) {
            static::$instance = new KeycloakWeb();
        }

        return static::$instance;
    }

    /**
     * Return the login URL
     *
     * @link https://openid.net/specs/openid-connect-core-1_0.html#CodeFlowAuth
     *
     * @return string
     */
    public function getLoginUrl()
    {
        $url = $this->getOpenIdValue('authorization_endpoint');
        $params = [
            'scope' => 'openid',
            'response_type' => 'code',
            'client_id' => $this->getClientId(),
            'redirect_uri' => $this->callbackUrl,
            'state' => $this->getState(),
        ];

        return $this->buildUrl($url, $params);
    }

    /**
     * Return the logout URL
     *
     * @return string
     */
    public function getLogoutUrl()
    {
        $url = $this->getOpenIdValue('end_session_endpoint');

        $redirectLogout = $this->redirectLogout; // url($this->redirectLogout);

        $token = $this->retrieveToken();
        if (empty($token) || empty($token['access_token'])) {
            return '';
        }

        $token = new KeycloakAccessToken($token);
        $idTokenHint = $token->getIdToken();

        $params = [
            'post_logout_redirect_uri' => $redirectLogout,
            'id_token_hint' => $idTokenHint
        ];

        return $this->buildUrl($url, $params);
    }

    /**
     * Return the register URL
     *
     * @link https://stackoverflow.com/questions/51514437/keycloak-direct-user-link-registration
     *
     * @return string
     */
    public function getRegisterUrl()
    {
        $url = $this->getLoginUrl();
        return str_replace('/auth?', '/registrations?', $url);
    }

    /**
     * Get access token from Code
     *
     * @param  string $code
     * @return array
     */
    public function getAccessToken($code)
    {
        $url = $this->getOpenIdValue('token_endpoint');
        $params = [
            'code' => $code,
            'client_id' => $this->getClientId(),
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->callbackUrl,
        ];

        if (!empty($this->clientSecret)) {
            $params['client_secret'] = $this->clientSecret;
        }

        try {
            $request = $this->getHttpClient()->takeFormData(true)->post($url, $params);

            if ($request->getCode() === 200) {
                return $request->getData(true);
            }
        } catch (Throwable $e) {
            $this->logException($e);

            throw $e;
        }
    }

    protected function postRequest($url, $params)
    {
        try {
            $request = $this->getHttpClient()->post($url, $params);

            if ($request->getCode() === 200) {
                return $request->getData(true);
            }

        } catch (Throwable $e) {
            $this->logException($e);

            throw $e;
        }
    }

    public function getAccessTokenByClientCredentials()
    {
        $url = $this->getOpenIdValue('token_endpoint');
        $params = [
            'client_id' => $this->getClientId(),
            'grant_type' => 'client_credentials',
            'redirect_uri' => $this->callbackUrl,
            'client_secret' => $this->clientSecret
        ];

        return $this->postRequest($url, $params);
    }

    public function getAccessTokenByPassword($username, $password)
    {
        $url = $this->getOpenIdValue('token_endpoint');
        $params = [
            'grant_type' => 'password',
            'client_id' => $this->getClientId(),
            'client_secret' => $this->clientSecret,
            'username' => $username,
            'password' => $password,
            'scope' => 'openid'
        ];

        return $this->postRequest($url, $params);
    }

    /**
     * Refresh access token
     *
     * @param $credentials
     * @return array
     * @throws Throwable
     */
    public function refreshAccessToken($credentials)
    {
        if (empty($credentials['refresh_token'])) {
            return [];
        }

        $url = $this->getOpenIdValue('token_endpoint');
        $params = [
            'client_id' => $this->getClientId(),
            'grant_type' => 'refresh_token',
            'refresh_token' => $credentials['refresh_token'],
            'redirect_uri' => $this->callbackUrl,
        ];

        if (!empty($this->clientSecret)) {
            $params['client_secret'] = $this->clientSecret;
        }

        return $this->postRequest($url, $params);
    }

    protected function postRequestWithToken($url, $params)
    {
        $httpClient = $this->getHttpClient();

        $token = new KeycloakAccessToken($this->retrieveToken());

        if (empty($token->getAccessToken())) {
            throw new Exception('Access Token is invalid.');
        }

        $claims = [
            'aud' => $this->getClientId(),
            'iss' => $this->getOpenIdValue('issuer'),
        ];

        $token->validateIdToken($claims);

        // Get userinfo
        $url = $this->getOpenIdValue('userinfo_endpoint');

        $httpClient->setHeaders([
            'Authorization' => 'Bearer ' . $token->getAccessToken(),
            'Accept' => 'application/json',
        ]);

        return $httpClient->post($url, $params);
    }

    /**
     * Invalidate Refresh
     *
     * @param  string $refreshToken
     * @return bool
     */
    public function invalidateRefreshToken($refreshToken)
    {
        $url = $this->getOpenIdValue('end_session_endpoint');
        $params = [
            'client_id' => $this->getClientId(),
            'refresh_token' => $refreshToken,
        ];

        if (!empty($this->clientSecret)) {
            $params['client_secret'] = $this->clientSecret;
        }

        try {
            $response = $this->httpClient->post($url, $params);
            return $response->getStatusCode() === 204;
        } catch (\Throwable $e) {
            $this->logException($e);
        }

        return false;
    }

    /**
     * Get access token from Code
     * @param  array $credentials
     * @return array
     */
    public function getUserProfile($credentials)
    {
        $credentials = $this->refreshTokenIfNeeded($credentials);

        $user = [];
        try {
            // Validate JWT Token
            $token = new KeycloakAccessToken($credentials);

            if (empty($token->getAccessToken())) {
                throw new Exception('Access Token is invalid.');
            }

            $claims = [
                'aud' => $this->getClientId(),
                'iss' => $this->getOpenIdValue('issuer'),
            ];

            $token->validateIdToken($claims);

            // Get userinfo
            $url = $this->getOpenIdValue('userinfo_endpoint');

            $request = $this->getHttpClient()->setHeaders([
                'Authorization' => 'Bearer ' . $token->getAccessToken(),
                'Accept' => 'application/json',
            ])->post($url);

            if ($request->getCode() !== 200) {
                throw new Exception('Was not able to get userinfo (not 200)');
            }

            $user = $request->getData(true);

            // Validate retrieved user is owner of token
            $token->validateSub($user['sub'] ?? '');
        } catch (\Throwable $e) {
            $this->logException($e);
        }

        return $user;
    }

    /**
     * Retrieve Token from Session
     *
     * @return array|null
     */
    public function retrieveToken()
    {
        return $this->session->get(self::KEYCLOAK_SESSION);
    }

    /**
     * Save Token to Session
     *
     * @return void
     */
    public function saveToken($credentials)
    {
        $this->session->put(self::KEYCLOAK_SESSION, $credentials);
        $this->session->save();
    }

    /**
     * Remove Token from Session
     *
     * @return void
     */
    public function forgetToken()
    {
        $this->session->forget(self::KEYCLOAK_SESSION);
        $this->session->save();
    }

    /**
     * Validate State from Session
     *
     * @return bool
     */
    public function validateState($state)
    {
        $challenge = $this->session->get(self::KEYCLOAK_SESSION_STATE);

        return (
            !empty($state)
            && !empty($challenge)
            && (
                $challenge === $state
                || str_starts_with($state, $challenge)
            )
        );
    }

    /**
     * Save State to Session
     *
     * @return void
     */
    public function saveState()
    {
        print_r($this->state);
        $this->session->put(self::KEYCLOAK_SESSION_STATE, $this->state);
        $this->session->save();
    }

    /**
     * Remove State from Session
     *
     * @return void
     */
    public function forgetState()
    {
        $this->session->forget(self::KEYCLOAK_SESSION_STATE);
        $this->session->save();
    }

    /**
     * Build a URL with params
     *
     * @param  string $url
     * @param  array $params
     * @return string
     */
    public function buildUrl($url, $params)
    {
        $parsedUrl = parse_url($url);
        if (empty($parsedUrl['host'])) {
            return trim($url, '?') . '?' . http_build_query($params);
        }

        if (!empty($parsedUrl['port'])) {
            $parsedUrl['host'] .= ':' . $parsedUrl['port'];
        }

        $parsedUrl['scheme'] = (empty($parsedUrl['scheme'])) ? 'https' : $parsedUrl['scheme'];
        $parsedUrl['path'] = (empty($parsedUrl['path'])) ? '' : $parsedUrl['path'];

        $url = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
        $query = [];

        if (!empty($parsedUrl['query'])) {
            $parsedUrl['query'] = explode('&', $parsedUrl['query']);

            foreach ($parsedUrl['query'] as $value) {
                $value = explode('=', $value);

                if (count($value) < 2) {
                    continue;
                }

                $key = array_shift($value);
                $value = implode('=', $value);

                $query[$key] = urldecode($value);
            }
        }

        $query = array_merge($query, $params);

        return $url . '?' . http_build_query($query);
    }

    /**
     * Преобразует массив в строку запроса URL
     *
     * @param array $array Массив параметров
     * @return string Строка запроса
     */
    function array_to_query(array $array): string
    {
        $parts = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $parts[] = urlencode($key) . '[]=' . urlencode($item);
                }
            } else {
                $parts[] = urlencode($key) . '=' . urlencode($value);
            }
        }

        return implode('&', $parts);
    }

    /**
     * Return the client id for requests
     *
     * @return string
     */
    protected function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Return the state for requests
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Return a value from the Open ID Configuration
     *
     * @param  string $key
     * @return string
     */
    protected function getOpenIdValue($key)
    {
        if (!$this->openid) {
            $this->openid = $this->getOpenIdConfiguration();
        }

        return $this->openid[$key] ?? null;
    }

    /**
     * Retrieve OpenId Endpoints
     *
     * @return array
     */
    protected function getOpenIdConfiguration()
    {
        $cacheKey = 'keycloak_web_guard_openid-' . $this->realm . '-' . md5($this->baseUrl);

        // From cache?
        if ($this->cacheOpenid == 'Y') {
            $configuration = $this->cache->get($cacheKey, []);

            if (!empty($configuration)) {
                return $configuration;
            }
        }

        // Request if cache empty or not using
        $url = $this->baseUrl . '/realms/' . $this->realm;
        $url = $url . '/.well-known/openid-configuration';

        $configuration = [];

        try {
            $request = $this->getHttpClient()->get($url);

            if ($request->getCode() === 200) {
                return $request->getData(true);
            }
        } catch (\Throwable $e) {
            $this->logException($e);

            throw new Exception('[Keycloak Error] It was not possible to load OpenId configuration: ' . $e->getMessage());
        }

        // Save cache
        if ($this->cacheOpenid === 'Y') {
            $this->cache->set($cacheKey, $configuration);
        }

        return $configuration;
    }

    /**
     * Check user permissions for resource
     *
     * @param string $permissions
     * @return bool
     */
    public function obtainPermissions($permissions, $clientId = null)
    {
        $url = $this->getOpenIdValue('token_endpoint');

        $token = $this->retrieveToken();
        if (empty($token) || empty($token['access_token'])) {
            return false;
        }

        $token = new KeycloakAccessToken($token);
        $accessToken = $token->getAccessToken();

        $params = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:uma-ticket',
            'audience' => $clientId ?? $this->clientId,
            'permission' => $permissions,
            'response_mode' => 'decision',
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        try {
            $response = $this->httpClient->request('POST', $url, ['form_params' => $params, 'headers' => $headers]);
            if ($response->getStatusCode() === 200) {
                return true;
            }
        } catch (GuzzleException $e) {
            // if ($e->getCode() === 403) {
            //     return false;
            // }
            $this->logException($e);

            // throw new Exception('[Keycloak Error] It was not possible to obtain permissions: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check we need to refresh token and refresh if needed
     *
     * @param  array $credentials
     * @return array
     */
    protected function refreshTokenIfNeeded($credentials)
    {
        if (!is_array($credentials) || empty($credentials['access_token']) || empty($credentials['refresh_token'])) {
            return $credentials;
        }

        $token = new KeycloakAccessToken($credentials);
        if (!$token->hasExpired()) {
            return $credentials;
        }

        $credentials = $this->refreshAccessToken($credentials);

        if (empty($credentials['access_token'])) {
            $this->forgetToken();
            return [];
        }

        $this->saveToken($credentials);
        return $credentials;
    }

    /**
     * Log a GuzzleException
     *
     * @param  Throwable $e
     * @return void
     */
    protected function logException(\Throwable $e)
    {

    }

    protected function takeGetRequest($url, $params = [])
    {
        $token = $this->retrieveToken();

        $token = new KeycloakAccessToken($token);
        $accessToken = $token->getAccessToken();

        if (empty($token) || empty($token['access_token'])) {
            return [];
        }

        $httpClient = $this->getHttpClient();

        $httpClient->setHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
        ]);

        $request = $httpClient->get($url . '?' . http_build_query($params));

        if ($request->getCode() === 200) {
            return $request->getData(true);
        }

        throw new Exception('[Keycloak] Request exception');
    }

    /**
     * Return a random state parameter for authorization
     *
     * @return string
     */
    protected function generateRandomState()
    {
        return bin2hex(random_bytes(16));
    }

    protected function getHttpClient()
    {
        return new HttpClient();
    }

    public static function redirectToLogin()
    {
        $url = static::instance()->getLoginUrl();
        static::instance()->saveState();

        header("Location: $url");
        exit();
    }
}

