<?php

class KeycloakWebGuard
{
    protected static $instance = null;

    /**
     * @var CUser
     */
    protected $user;


    protected $provider;

    public function __construct(&$user)
    {
        $this->user = $user;
        $this->provider = new KeycloakWebUserProvider();
    }

    /**
     * @return static
     */
    public static function instance()
    {
        if (static::$instance === null) {
            global $USER;
            static::$instance = new KeycloakWebGuard($USER);
        }

        return static::$instance;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return $this->user->IsAuthorized() && $this->checkCredentials();
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return !$this->check();
    }

    public function checkCredentials()
    {
        // Get Credentials
        $credentials = KeycloakWeb::instance()->retrieveToken();

        if (empty($credentials)) {
            return false;
        }

        global $USER;

        $user = KeycloakWeb::instance()->getUserProfile($credentials);

        if (empty($user)) {
            KeycloakWeb::instance()->forgetToken();

            return false;
        } elseif ($USER->IsAuthorized() && $user['preferred_username'] !== mb_strtolower($USER->GetLogin())) {
            session_unset();
            session_destroy();
            $USER->Logout();

            return false;
        }

        return $user;
    }

    /**
     * Try to authenticate the user
     *
     * @return boolean
     */
    public function authenticate()
    {
        $user = $this->checkCredentials();
        if ($user === false) {
            return false;
        }

        // Provide User
        $user = $this->provider->retrieveByCredentials($user);

        if (empty($user) || $user['ACTIVE'] !== 'Y') {
            echo "Вам ограничен доступ к этому сайту";
            exit();
        }

        $this->user->Authorize(+$user["ID"], true);

        return true;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     *
     * @throws BadMethodCallException
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials['access_token']) || empty($credentials['id_token'])) {
            return false;
        }

        /**
         * Store the section
         */
        $credentials['refresh_token'] = $credentials['refresh_token'] ?? '';
        KeycloakWeb::instance()->saveToken($credentials);

        return $this->authenticate();
    }

}